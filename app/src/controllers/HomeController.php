<?php

namespace pictogin\controllers;

use pictogin\IController;
use Slim\App;

/**
 * Class HomeController
 * @package pictogin\controllers
 * Various global endpoints for the application.
 */
class HomeController implements IController{
    /**
     * @param $app App
     * @param $config array
     */
    public function register(App $app, array $config) {
        $app->get('/', function ($request, $response) {
            return $this->view->render($response, 'home.twig', []);
        })->setName('home');

        if (getenv('DEBUG')) {
            $app->get('/phpinfo', function () {
                ob_start();
                phpinfo();
                $phpInfo = ob_get_contents();
                ob_get_clean();
                return $phpInfo;
            });
        }
    }
}