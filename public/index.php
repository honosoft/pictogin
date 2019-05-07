<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new pictogin\Pictogin())->run();

/*
use pictogin\controllers\UserController;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

// TODO: move the content of this file to SRC/

session_start();

$config = require_once('../configs/app.php');
$app = new App();
$container = $app->getContainer();
$container['view'] = function ($container) {
    $view = new Twig(__DIR__ . '/../app/templates', [
        'cache' => false // TODO: for debug, no cache.
    ]);

    // set global variables
    if (isset($_SESSION['user'])) {
        $view->getEnvironment()->addGlobal("user", $_SESSION['user']);
    }

    // Instantiate and add Slim specific extensions
    $router = $container->get('router');
    $uri = Uri::createFromEnvironment(new Environment($_SERVER));
    $view->addExtension(new TwigExtension($router, $uri));

    // TODO: add ctrf token

    return $view;
};

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


UserController::register($app, $config);


// image merge and create a image map?
// https://www.codepunker.com/blog/how-to-merge-png-files-with-php-and-GD-Library

// Run app
$app->run(); // TODO: might throw exceptions
*/