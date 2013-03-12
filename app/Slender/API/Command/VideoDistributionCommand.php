<?php

namespace Slender\API\Command;

use Dws\Slender\Api\Auth\Permissions;
use Slender\API\Model\Sites;
use Slender\API\Model\Videodistributions;
use Slender\API\Model\Videos;
use Slender\API\Model\Youtubechannels;
use Slender\API\Model\Youtubeplaylists;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpKernel\Client as BaseClient;
use \App;
use OpenCloud\OpencloudClient;
use \Illuminate\Support\Facades\Config;
use YoutubeClient;

class VideoDistributionCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'process-video-queues';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Upload video queue to youtube and change statuses of videos';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
        $this->info('Proccessing video queue');

        $sites = array();

        $sitesModel = new Sites();
        $videoDistributionModel = new Videodistributions();
        $videosModel = new Videos();
        $channelsModel = new Youtubechannels();
        $playlistsModel = new Youtubeplaylists();

        $workerConfig = Config::get('worker.ditributions');

        //Getting list of sites to process video queues
        $sitesOption = $this->option('sites'); //Get --sites option from command line
        if ($sitesOption) {
            $siteSlugs = explode(',' , $sitesOption);
            if (count($siteSlugs)) {
                foreach ($siteSlugs as $slug) {
                    $sites[] = $sitesModel->getCollection()->where('slug', $slug)->first();
                }
            }
        }
        else {
            $sitesCursor = $sitesModel->getCollection()->get();
            foreach ($sitesCursor as $el) {
                $sites[] = $el;
            }
        }

        if (count($sites)) {
            foreach ($sites as $site) {
                $this->info('Proccessing site: ' . $site['slug']);

                //Creating new mongo connection
                $connection = App::make('mongo')->connection($site['slug']);

                //Setting new connection
                $videosModel->setConnection($connection);
                $videoDistributionModel->setConnection($connection);
                $channelsModel->setConnection($connection);
                $playlistsModel->setConnection($connection);

                $queuedDistrCursor = $videoDistributionModel->getCollection()
                    ->where('status', Videodistributions::STATUS_QUEUED)
                    ->andWhere(function($query)
                    {
                        $query->whereLt('later', new \MongoDate())
                            ->orWhereIn('later', array(null));
                    })
                    ->andWhereLt('attempts', $workerConfig['attempts_number'])
                    ->get();

                foreach ($queuedDistrCursor as $queuedDistr) {
                    $videoDistributionModel->update(
                        $queuedDistr['_id'],
                        array(
                            'updated' => new \MongoDate(),
                            'status' => Videodistributions::STATUS_IN_PROGRESS,
                            'later' => new \MongoDate(strtotime("+" . $workerConfig['attempts_frequency'] . " minutes"))
                        )
                    );

                    $video = $videosModel->getCollection()           //Getting video of distribution
                        ->where('_id', $queuedDistr['video_id'])
                        ->first();

                    $playlist = $playlistsModel->getCollection()       //Getting playlist of distribution
                        ->where('_id', $queuedDistr['distribution_id'])
                        ->first();

                    if ($playlist && $playlist['_id']) {
                        $channel = $channelsModel->getCollection()       //Getting channel of distribution
                            ->where('_id', $playlist['channel']['id'])
                            ->first();
                    }

                    $this->info('Processing Video id: ' . $video['_id']);

                    if (
                        $video && $playlist && $channel &&
                        $video['_id'] && $playlist['_id'] && $channel['_id']
                    ) {
                        if ($video['cloud_filename']) {
                            $cloudConfig = Config::get('rackspace.opencloud');
                            $path = $cloudConfig['localPath'] . DIRECTORY_SEPARATOR . $video['cloud_filename'];

                            //Download video
                            $rackspace = new OpencloudClient($cloudConfig);
                            $rackspace->downloadFile(
                                $path,
                                $video['cloud_filename']
                            );

                            $videoData = array(
                                //path to file
                                'source' => $path,
                                'mime' => mime_content_type($path),
                                'slug' => basename($path),
                                'title' => basename($path),
                                'description' => 'Description',
                                //must be valid youtube video category
                                'category' => 'Autos',
                                // Please note that this must be a comma-separated string
                                // and that individual keywords cannot contain whitespace
                                'tags' => 'cars'
                            );

                            try {
                                $youtubeClient = new YoutubeClient(
                                    $channel['youtubeEmail'],
                                    $channel['youtubePass'],
                                    $channel['apiKey'],
                                    $channel['apiName']
                                );
                            } catch (\Zend_Exception $e) {
                                $videoDistributionModel->update(
                                    $queuedDistr['_id'],
                                    array(
                                        'status' => Videodistributions::STATUS_QUEUED,
                                        'attempts' => (int)$queuedDistr['attempts'] + 1,
                                        'updated' => new \MongoDate()
                                    )
                                );

                                mail(
                                    $workerConfig['errors_recipient_email'],
                                    $workerConfig['error_email_subjects'],
                                    'Wrong youtube channel config. Channel id: ' . $channel['_id']
                                );

                                $this->error('Wrong youtube channel config. Channel id: ' . $channel['_id']);

                                continue;
                            }

                            $ytVidId = null;

                            //Uploading video
                            try {
                                $youtubeClient->insertVideo($videoData, $playlist['alias']);

                                $ytVidId = $youtubeClient->getVideoId();
                                $ytVidUrl = $youtubeClient->getVideoUrl();
                                $ytVidThumb = $youtubeClient->getVideoThumb();

                            } catch (\Zend_Exception $e) {
                                $videoDistributionModel->update(
                                    $queuedDistr['_id'],
                                    array(
                                        'status' => Videodistributions::STATUS_QUEUED,
                                        'attempts' => (int)$queuedDistr['attempts'] + 1,
                                        'updated' => new \MongoDate()
                                    )
                                );

                                mail(
                                    $workerConfig['errors_recipient_email'],
                                    $workerConfig['error_email_subjects'],
                                    'Cannot upload video ' . $video['_id']
                                );

                                $this->error('Cannot upload video ' . $video['_id']);

                                continue;
                            }

                            if ($ytVidId) {
                                $videoDistributionModel->update(
                                    $queuedDistr['_id'],
                                    array(
                                        'status' => Videodistributions::STATUS_PUBLISHED,
                                        'attempts' => 0,
                                        'updated' => new \MongoDate()
                                    )
                                );

                                $videosModel->update(
                                    $video['_id'],
                                    array(
                                        'urls' => array(
                                            'source' 	 => $ytVidUrl,
                                            'streaming'  => $ytVidUrl,
                                            'thumbnail'  => $ytVidThumb,
                                        ),
                                        'updated' => new \MongoDate()
                                    )
                                );
                            }
                            else {
                                $videoDistributionModel->update(
                                    $queuedDistr['_id'],
                                    array(
                                        'status' => Videodistributions::STATUS_QUEUED,
                                        'attempts' => (int)$queuedDistr['attempts'] + 1,
                                        'updated' => new \MongoDate()
                                    )
                                );

                                mail(
                                    $workerConfig['errors_recipient_email'],
                                    $workerConfig['error_email_subjects'],
                                    'Video wasn\'t uploaded to youtube. Video Id: ' . $video['_id']
                                );

                                $this->error('Video wasn\'t uploaded to youtube');
                                continue;
                            }
                        }
                        else {
                            $videoDistributionModel->update(
                                $queuedDistr['_id'],
                                array(
                                    'status' => Videodistributions::STATUS_QUEUED,
                                    'attempts' => (int)$queuedDistr['attempts'] + 1,
                                    'updated' => new \MongoDate()
                                )
                            );

                            mail(
                                $workerConfig['errors_recipient_email'],
                                $workerConfig['error_email_subjects'],
                                'Video wasn\'t uploaded to cloud. Video Id: ' . $video['_id']
                            );

                            $this->error('Video wasn\'t uploaded to cloud');
                            continue;
                        }
                    }
                    else {
                        $videoDistributionModel->update(
                            $queuedDistr['_id'],
                            array(
                                'status' => Videodistributions::STATUS_QUEUED,
                                'attempts' => (int)$queuedDistr['attempts'] + 1,
                                'updated' => new \MongoDate()
                            )
                        );

                        mail(
                            $workerConfig['errors_recipient_email'],
                            $workerConfig['error_email_subjects'],
                            'Wrong channel or playlist identifier. Video distribution should be updated. <br/>
                            Video Id: ' . $video['_id']
                        );

                        $this->error('Wrong channel or playlist identifier. Video distribution should be updated');
                        continue;
                    }
                }
            }
        }
        else {
            $this->error('No sites founded');
        }

        $this->info('Proccessing video queue finished');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
            array('sites', 'sites', InputOption::VALUE_OPTIONAL, 'list of slugs separated with comma', '')
        );
	}
}