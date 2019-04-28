<?php

namespace pictogin\controllers;

use pictogin\images\ImageFactory;
use pictogin\QuickDb;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class UserController {
    private static $config;
    private static $app;

    /**
     * @param $app App
     * @param $config
     */
    public static function register($app, $config) {
        static::$config = $config;

        $app->get('/login', function ($request, $response) {
            session_destroy();
            return $this->view->render($response, 'login.twig', []);
        })->setName('login');

        $app->post('/login', function ($request, $response) {
            UserController::postLogin($this, $request, $response); // NOTE: for some reason the callable [UserController::class, 'postLogin'] is not working.
        });

        $app->get('/signup', function ($request, $response) {
            session_destroy();
            return $this->view->render($response, 'signup.twig', []);
        })->setName('signup');

        $app->post('/signup', function ($request, $response) {
            UserController::postSignup($this, $request, $response); // NOTE: for some reason the callable [UserController::class, 'postLogin'] is not working.
        });

        $app->get('/thanks', function ($request, $response) {
            if (!isset($_SESSION['signup'])) {
                return $response->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }

            // TODO: validate that the user was inserted!

            $selectedImages = $_SESSION['signup']['selected']; // TODO: find the real url - may be bigger images stackable?
            $email = $_SESSION['signup']['email'];
            unset($_SESSION['signup']); // clear the login session!

            return $this->view->render($response, 'thanks.twig', ['images' => $selectedImages, 'email' => $email]);
        })->setName('signup');

        $app->post('/user/magic-link', function ($request, $response) {
            // TODO: use the send mail and twig template to send an e-mail - use the session email.
            return "not implemented";
        });
    }

    /**
     * @param $app App
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @return mixed
     */
    private static function postLogin($app, $request, $response) {
        if (isset($_SESSION['user'])) {
            return $response->withStatus(302)->withHeader('Location', $app->router->pathFor('home'));
        }

        $body = $request->getParsedBody();
        if (isset($_SESSION['login'])) {
            if ($_SESSION['login']['step'] >= 3) { // TODO: constant in a file.

                // TODO: check and send to an error page if one mistake in the 3...

                static::initUser($_SESSION['login']['email']);

                unset($_SESSION['login']); // clear the login session!
                return $response->withStatus(302)->withHeader('Location', $app->router->pathFor('home'));
            } else {
                $_SESSION['login']['step']++;
                $_SESSION['login']['selected'][] = $body['choice'];
            }
        } else {
            $errorMessage = "The e-mail entered is not registered on pictogin. Please use sign-up to create a new account.";
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                return $app->view->render($response, 'login.twig', ['error' => $errorMessage]);
            }

            $db = new QuickDb();
            $user = $db->getUser($body['email']);
            if (!$user) {
                return $app->view->render($response, 'login.twig', [
                    'action' => '/login',
                    'error' => $errorMessage
                ]);
            }
            if ($user['password_retries'] > static::$config['login']['retry_count']) {
                return $app->view->render($response, 'login.twig', [
                    'action' => '/login',
                    'error' => "Your account is locked. Please use the 'Forgot Password'."
                ]);
            }

            // Initialize the session for the first time.
            $_SESSION['login'] = [
                'step' => 1,
                'email' => $body['email'],
                'selected' => [],
                'generated' => [] // TODO: generate the order from the $user [shuffle and pop random [-1, 0]]
            ];
        }

        $_SESSION['login']['images'] = static::generateImages(); // TODO: be sure to render using an existing image or not.

        return $app->view->render($response, 'login.twig', [
            'action' => '/login',
            'email' => $_SESSION['login']['email'],
            'images' => array_map(function($item) { return $item['url']; }, $_SESSION['login']['images']),
            'step'  => $_SESSION['login']['step'],
            'columnCount' => static::$config['login']['images']['column-count']
        ]);
    }

    /**
     * @param $app App
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @return mixed
     * @throws \Exception
     */
    private static function postSignup($app, $request, $response) {
        if (isset($_SESSION['user'])) {
            return $response->withStatus(302)->withHeader('Location', $app->router->pathFor('home'));
        }

        $body = $request->getParsedBody();
        if (isset($_SESSION['signup'])) {
            if ($_SESSION['login']['step'] >= 3) { // TODO: constant in a file.

                $db = new QuickDb();
                $db->addUser($_SESSION['signup']['email'], $_SESSION['signup']['selected']);

                // TODO: Send to thank you page and "use" the ids in the session and unset it after.
                return $response->withStatus(302)->withHeader('Location', $app->router->pathFor('home'));
            } else {
                if (isset($body['choice']) && $body['choice']) {
                    $_SESSION['signup']['step']++;
                    $_SESSION['signup']['selected'][] = $_SESSION['signup']['images'][intval($body['choice'])];
                }
            }
        } else {
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                return $app->view->render($response, 'signup.twig', [
                        'error' => "The e-mail entered is invalid."]
                );
            }

            $db = new QuickDb();
            $user = $db->getUser($body['email']);
            if ($user) {
                return $app->view->render($response, 'signup.twig', ['error' => "The e-mail already exists."]);
            }

            $_SESSION['signup'] = [
                'step' => 1,
                'email' => $body['email'],
                'selected' => []
            ];
        }

        $images = static::generateImages(); // TODO: be sure not to show the same images...;
        $_SESSION['signup']['images'][] = $images;

        return $app->view->render($response, 'signup.twig', [
            'email' => $_SESSION['signup']['email'],
            'images' => array_map(function($item) { return $item['url']; }, $images),
            'step'  => $_SESSION['signup']['step'],
            'columnCount' => static::$config['login']['images']['column-count']
        ]);
    }

    private static function generateImages() : array {
        // TODO: if in login mode, be sure to pass an existing image to randomize in the list.
        return static::fetchImages();
    }

    private static function initUser($email, $db = null) {
        if ($db == null) {
            $db = new QuickDb();
        }

        $_SESSION['user'] = $db->getUser($email);
         // NOTE: add other user info here as well.
    }

    private static function fetchImages() {
        $factory = ImageFactory::create();
        return $factory->fetch(static::$config['login']['images']['count']);
    }
}