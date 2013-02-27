<?php

namespace Slender\API\Command;

use Dws\Slender\Api\Auth\Permissions;
use Slender\API\Model\Sites;
use Slender\API\Model\Videodistributions;
use Slender\API\Model\Videos;
use Slender\API\Model\Youtubechannels;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
//use Symfony\Component\Console\Input\InputArgument;
//use Symfony\Component\HttpKernel\Client as BaseClient;

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
                //Setting up the current site
                $videoDistributionModel->setSite($site['slug']);
                $videosModel->setSite($site['slug']);
                $channelsModel->setSite($site['slug']);

                $queuedDistrCursor = $videoDistributionModel->getCollection()
                    ->where('status', Videodistributions::STATUS_QUEUED)->get(); //TODO: or put statements to where
                foreach ($queuedDistrCursor as $queuedDistr) {
                    if (true) { //TODO: statements (attempts, date etc)
                        $video = $videosModel->getCollection()           //Getting video of distribution
                            ->where('slug', $queuedDistr['video_id'])
                            ->first();
                        $channel = $channelsModel->getCollection()       //Getting channel of distribution
                            ->where('slug', $queuedDistr['distribution_id'])
                            ->first();

                        $this->info('Video id' . $video['_id']);
                        $this->info('Channel id' . $channel['_id']);
                        if ($video['_id'] && $channel['_id']) {
                            //TODO: upload video to youtube
                            //TODO: if succes update distribution status with Videodistributions::STATUS_PUBLISHED
                            //TODO: if error increment distribution attempts
                        }
                        else {
                            $this->error('Cannot find video or channel');
                        }
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