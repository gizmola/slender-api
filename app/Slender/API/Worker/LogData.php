<?php namespace Dws\Queue\Worker;

/**
* jsamos@gmail.com
* this class is mainly for "out of the box"
* demostation, and testing
*/


class LogData
{

    /**
    * fire (reuired worker function by laravel)
    * @param $job
    * @param array $data
    */
    public function fire($job, $data)
    {
        $handle = fopen($data['path'], 'w');
        fwrite($handle , json_encode($data['data']));
        $job->delete();
    }
    
}