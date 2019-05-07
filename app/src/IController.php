<?php

namespace pictogin;

use Slim\App;

interface IController {
    /**
     * @param App $app
     * @param array $config from the file '/configs/App.php'
     * @return void
     */
    public function register(App $app, array $config);
}