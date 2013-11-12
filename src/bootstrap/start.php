<?php

use Slender\Api\Config\SlenderConfig;

$config = new SlenderConfig(App::getConfigLoader(), App::environment());
App::instance('slender-config', $config);