<?php

return array(
    'base-config-file' => __DIR__ . '/resources.php',
    'psr-0' => app_path(),
    'templates' => [
        'model' => app_path() . "/templates/model.template.php",
        'controller' => app_path() . "/templates/controller.template.php",
    ],
);