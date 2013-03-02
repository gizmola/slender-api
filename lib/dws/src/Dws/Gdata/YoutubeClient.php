<?php
class YoutubeClient {

    protected $youtubeEmail = null;
    protected $youtubePassword = null;
    protected $apiKey = null;
    protected $apiName = null;
    protected $videoId = null;
    protected $videoUrl = null;
    protected $videoThumb = null;

    protected $client;

    public function __construct($youtubeEmail, $youtubePassword, $apiKey, $apiName)
    {
        $this->youtubeEmail    = $youtubeEmail;
        $this->youtubePassword = $youtubePassword;
        $this->apiKey          = $apiKey;
        $this->apiName         = $apiName;

        $this->auth();
    }

    public function auth(){
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        $this->client = Zend_Gdata_ClientLogin::getHttpClient(
            $this->youtubeEmail ,
            $this->youtubePassword,
            'youtube'
        );
    }

    /**
     * Direct upload of video to Youtube
     *
     * @param array $videoData
     * Example
    $videoData = array(
    //path to file
    'source' => __DIR__. '/' . 'test.avi',
    'mime' => 'video/x-msvideo',
    'slug' => 'test.avi',
    'title' => 'Title',
    'description' => 'Testing youtube api',
    //must be valid youtube video category
    'category' => 'Autos',
    // Please note that this must be a comma-separated string
    // and that individual keywords cannot contain whitespace
    'tags' => 'cars, funny'
    );
     * @return false|string response url
     */
    public function insertVideo($videoData)
    {
        Zend_Loader::loadClass('Zend_Gdata_YouTube');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_HttpClient');
        Zend_Loader::loadClass('Zend_Http_Client_Adapter_Curl');
        Zend_Loader::loadClass('Zend_Gdata_Youtube_VideoEntry');
        Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');

        $yt = new Zend_Gdata_YouTube($this->client, $this->apiName, 'Video Upload', $this->apiKey);
        $myVideoEntry = new Zend_Gdata_Youtube_VideoEntry();

        $filesource = $yt->newMediaFileSource($videoData['source']);
        $filesource->setContentType($videoData['mime']);
        $filesource->setSlug($videoData['slug']);

        $myVideoEntry->setMediaSource($filesource);
        $myVideoEntry->setVideoTitle($videoData['title']);
        $myVideoEntry->setVideoDescription($videoData['description']);
        $myVideoEntry->setVideoCategory($videoData['category']);
        $myVideoEntry->SetVideoTags($videoData['tags']);

        $uploadUrl = 'http://uploads.gdata.youtube.com/feeds/users/' . $this->apiName . '/uploads';
        $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_Youtube_VideoEntry');

        $this->videoId = $newEntry->getVideoId();
        $videoThumbnails = $newEntry->getVideoThumbnails();
        $this->videoThumb = $videoThumbnails[1]['url'];
        $this->videoUrl = $newEntry->getVideoWatchPageUrl();
    }

    public function getVideoId() {
        return $this->videoId;
    }

    public function getVideoUrl() {
        return $this->videoUrl;
    }

    public function getVideoThumb() {
        return $this->videoThumb;
    }
}


