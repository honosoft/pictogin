<?php

namespace pictogin\controllers;

use pictogin\images\ImageFactory;
use pictogin\MailClient;
use pictogin\QuickDb;
use pictogin\SimpleLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class UserController {
    private static $config;

    /**
     * @param $app App
     * @param $config
     */
    public static function register($app, $config) {
        static::$config = $config;

        $app->get('/login', function ($request, $response) {
            session_destroy();
            // need to override the user parameter since it's destroyed and was set at the initialization.
            return $this->view->render($response, 'login.twig', ['user' => null]);
        })->setName('login');

        $app->post('/login', function ($request, $response) {
            UserController::postLogin($this, $request, $response); // NOTE: for some reason the callable [UserController::class, 'postLogin'] is not working.
        });

        // TODO: should we add a real log out page with a 'see you next time!'.

        $app->get('/signup', function ($request, $response) {
            session_destroy();
            return $this->view->render($response, 'signup.twig', []);
        })->setName('signup');

        $app->post('/signup', function ($request, $response) {
            UserController::postSignup($this, $request, $response);
        });

        $app->post('/user/magic-link', function ($request, $response) {
            // TODO: use the send mail and twig template to send an e-mail - use the session email.
            return "not implemented";
        });

        $app->get('/test', function() {
            return 'OK';
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
        $db = new QuickDb();

        if (isset($_SESSION['login'])) {
            SimpleLogger::log("1 - now asking for password.");
            $email = $_SESSION['login']['email'];
            $user = $db->getUser($email);
            $_SESSION['login']['selected'][] = explode(',', $body['choice']);

            if ($_SESSION['login']['phase'] >= static::$config['login']['phases']['count']) {
                SimpleLogger::log("  1.1 - All phases completed.");

                $diff = array_diff($_SESSION['login']['generated'], $_SESSION['login']['selected']);
                if (count($diff) > 0) {
                    SimpleLogger::log("    1.1.1 - Choices doesn't match.");
                    $db->incrementPasswordRetries($email);
                    unset($_SESSION['login']); // clear the login session!
                    return $app->view->render($response, 'login.twig', [
                        'action' => '/login',
                        'error' => "Your account is locked. Please use the 'Forgot Password'."
                    ]);
                } else {
                    SimpleLogger::log("    1.1.2 - Login successful.");
                    $db->resetPasswordRetries($email);
                    static::initUser($email);
                    unset($_SESSION['login']); // clear the login session
                    return $response->withStatus(302)->withHeader('Location', $app->router->pathFor('home'));
                }
            } else {
                SimpleLogger::log("  1.2 - Incrementing phase.");
                $_SESSION['login']['phase']++;
            }
        } else {
            SimpleLogger::log("2 - Checking the e-mail.");
            $errorMessage = "The e-mail entered is not registered on pictogin. Please use sign-up to create a new account.";

            $email = $body['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $app->view->render($response, 'login.twig', ['error' => $errorMessage]);
            }

            $user = $db->getUser($email);
            if (!$user) {
                return $app->view->render($response, 'login.twig', [
                    'action' => '/login',
                    'error' => $errorMessage
                ]);
            }
            if ($user['password_retries'] > static::$config['login']['retry_count']) {
                unset($_SESSION['login']);
                return $app->view->render($response, 'login.twig', [
                    'action' => '/login',
                    'error' => "Your account is locked. Please use the 'Forgot Password'."
                ]);
            }

            // Initialize the session for the first time.
            $_SESSION['login'] = [
                'phase' => 1,
                'error' => false,
                'email' => $email,
                'selected' => [],
                'generated' => []
            ];
        }

        $_SESSION['login']['images'] = static::generateImages($user['password']);

        return $app->view->render($response, 'login.twig', [
            'action' => '/login',
            'email' => $_SESSION['login']['email'],
            'images' => array_map(function($item) { return $item['url']; }, $_SESSION['login']['images']),
            'step'  => $_SESSION['login']['phase'],
            'maxSteps' => static::$config['login']['phases']['count'],
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
            if (isset($body['choice'])) {
                $previousIndex = $_SESSION['signup']['step'] - 1;
                $previousImages = $_SESSION['signup']['images'][$previousIndex];
                $item = $previousImages[$body['choice']];

                $_SESSION['signup']['step']++;
                $_SESSION['signup']['selected'][] = $item['id'];
            } else {
                SimpleLogger::log("1.2.2");
                // TODO: should redirect to /signup?
            }

            if ($_SESSION['signup']['step'] > static::$config['signup']['images-count']) {
                $selectedImages = $_SESSION['signup']['selected'];
                $email = $_SESSION['signup']['email'];

                $db = new QuickDb();
                $db->addUser($email, $selectedImages);
                static::initUser($email, $db); // use default init mechanism.

                $mailClient = new MailClient();
                $mailClient->subject("Welcome to Pictogin!")
                    ->template('mails/welcome.twig', ['email' => $email])
                    ->send($email);

                unset($_SESSION['signup']); // remove unwanted cookies

                return $app->view->render($response, 'thanks.twig', [
                    "user", $_SESSION['user'], // NOTE: normally, it's implicit, but since its just been created, we have to pass it or redirect.
                    'images' => $selectedImages, // TODO: fetch the images by ids to display.
                    'email' => $email]);
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

        $images = static::fetchImages(static::$config['login']['images']['count']); // TODO: be sure not to show the same previous images...
        $_SESSION['signup']['images'][] = $images;

        return $app->view->render($response, 'signup.twig', [
            'email' => $_SESSION['signup']['email'],
            'images' => array_map(function($item) { return $item['url']; }, $images),
            'step'  => $_SESSION['signup']['step'],
            'maxSteps'  => static::$config['signup']['images-count'],
            'columnCount' => static::$config['login']['images']['column-count']
        ]);
    }

    /**
     * Using all the parameters, return the list of available images
     * from the user password.
     * @param $userImages array The list of all images the user have saved in his profile.
     * @return array String of images.
     */
    private static function getRemainingUserImages($userImages) : array {
        // reduce the array
        $images = array_diff($userImages, $_SESSION['login']['generated']);
        shuffle($images);

        // find the number of items we want.
        $min = static::$config['login']['phases']['n'] - static::$config['login']['phases']['variation'];
        $max = static::$config['login']['phases']['n'];
        $count = min(rand($min, $max),count($images));
        SimpleLogger::log("{min: $min, max: $max, count: $count}");

        // return only the elements of the array.
        $ret = array_slice($images, 0, $count);
        SimpleLogger::log("user(" . json_encode($userImages) . ") - existing("
            . json_encode($_SESSION['login']['generated']) . ") = remaining("
            . json_encode($images) . ") => reduce("
            . json_encode($ret) . ")");
        return $ret;
    }

    /**
     * Given a base array (images), insert randomly the list of items (selectedImages).
     * @param $images array
     * @param $selectedImages array
     */
    private static function randomizeUserImages(&$images, $selectedImages) {
        $itemIndexes = range(0, count($images) - 1);
        shuffle($itemIndexes);

        $factory = ImageFactory::create();
        for ($i = 0; $i < count($selectedImages); $i++) {
            $images[$itemIndexes[$i]] = $factory->find($selectedImages[$i]);
        }
    }

    // --------- TODO: DEBUG THIS METHOD. -----------
    private static function generateImages(array $userImages) : array {
        SimpleLogger::log('Generating images');
        $selectedImages = static::getRemainingUserImages($userImages);
        $images = static::fetchImages(static::$config['login']['phases']['k']);
        $_SESSION['login']['generated'] = array_merge($_SESSION['login']['generated'], $selectedImages);

        // NOTE: if in a previous phase the user failed -> just use randomized content.
        if (!$_SESSION['login']['error']) {
            static::randomizeUserImages($images, $userImages);
        }

        return $images;
    }

    private static function initUser($email, $db = null) {
        if ($db == null) {
            $db = new QuickDb();
        }

        $_SESSION['user'] = $db->getUser($email);
         // NOTE: add other user info here as well.
    }

    private static function fetchImages($count) {
        $factory = ImageFactory::create();
        return $factory->fetch($count);
    }
}