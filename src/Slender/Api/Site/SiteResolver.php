<?php namespace Slender\Api\Site;

use App;

class SiteResolver {
    


    public function get()
    {

        if (App::runningInConsole()) {

            return $this->getSiteFromConsole()

        } else {

            return $this->getSiteFromUri();

        }

    }

    public function getSiteFromConsole()
    {

        
        $argv = Request::instance()->server->get('argv');

        foreach ($argv as $key => $value) {
            
            if (starts_with($value, '--site=')) {
                
                $segments = array_slice(explode('=', $value), 1);
                $site = head($segments);

            }

        }

        return (isset($site)) ? $site : null;

    }

    public function getSiteFromUri()
    {

        $segments = Request::segments();

        if (count($segments) > 1)
            return Request::segment(1);

        return null;

    }

}