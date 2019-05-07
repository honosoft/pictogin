<?php

namespace pictogin;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class Pictogin {

    private const CONTROLLER_PATH = __DIR__ . '/controllers';

    /** @var array */
    private static $config;
    /** @var App */
    private static $app;

    /**
     * Quick accessor for the global configuration.
     * @return array Application config located in '/configs/app.php'
     */
    public static function config() {
        return static::$config;
    }

    /**
     * Quick global accessor for this instance (Singleton like)
     * @return App
     */
    public static function app() {
        return static::$app;
    }

    /**
     * Pictogin constructor.
     * @throws Exception
     */
    public function __construct() {
        if (static::$app) {
            throw new Exception("Pictogin can only be instantiated once.");
        }

        session_start();
        static::$config = require_once(__DIR__ . '/../../configs/app.php');
        static::$app = new App();

        $this->initView(static::$app);
        $this->initControllers(static::$app, static::$config);
    }

    /**
     * Run the application (Slim)
     */
    public function run() {
        try {
            static::$app->run();
        } catch (Exception $e) {
            echo "Error loading the site.";
        }
    }

    private function initView(App $app) {
        // TODO: I think the view should be contained in another file and it should be easy to extend.
        $container = $app->getContainer();
        $container['view'] = function ($container) {
            $view = new Twig(__DIR__ . '/../templates', [
                'cache' => false // TODO: for debug, no cache.
            ]);

            // set global variables in twig.
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
    }

    /**
     * Initialize all the controllers found in the folder "controllers" recursively.
     * All class must implement the 'register($app, $config)' method (See \Pictogin\IController).
     * @param App $app
     * @param array $config
     * @throws Exception
     */
    private function initControllers(App $app, array $config) {
        $directory = new RecursiveDirectoryIterator(static::CONTROLLER_PATH);
        $iterator = new RecursiveIteratorIterator($directory);
        $regexIterator = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regexIterator as $path) {
            // NOTE: use the current folder architecture and create the namespace and class name using file convention.
            $class = '\\pictogin' . str_replace('/', '\\', substr($path[0], strlen(__DIR__), -4));
            if (!class_exists($class)) {
                throw new Exception("Class $class doesn't exists.");
            }

            $controller = new $class();
            if (!method_exists($controller, 'register')) {
                throw new Exception("register is not defined in your controller class.");
            }
            $controller->register($app, $config);
        }
    }
}