<?php

namespace pictogin\images\unsplash;

use pictogin\images\IImageFetcher;

class UnsplashFetcher implements IImageFetcher {

    /** @var array file config */
    private static $config = null;

    public function __construct() {
        if (!static::$config) {
            static::$config = require(getcwd() . '/../configs/unsplash.php');
        }
    }

    public function fetch(int $count) : array {
        $images = $this->query("/photos/random", "count=$count");
        return array_map([$this, 'reduce'], $images);
    }

    public function find(string $id) : array {
        $image = $this->query("/photos/$id");
        return $this->reduce($image);
    }

    private function reduce($item) {
        return [
            'id' => $item->id,
            'url' => $item->urls->thumb
        ];
    }

    private function query($action, $params = NULL) {
        $url = "https://api.unsplash.com/$action?client_id=" . static::$config['accessKey'];
        if ($params) {
            $url .= "&$params";
        }
        $opts = [
            'http'=>[
                'method' => "GET",
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\n" .
                            "Accept-Version: v1\r\n",
                            "Authorization: Client-ID " . static::$config['accessKey'] . "\r\n" // For some reason it doesn't work. Maybe the documentation is wrong. Added in query parameter.
            ]
        ];
        $context = stream_context_create($opts);
        $content = file_get_contents($url, false, $context);
        if (!$content) {
            $error = error_get_last();
            error_log($error['message']);
            throw new \Exception("Error doing the query.");
        }

        $obj = json_decode($content);
        if (isset($obj->errors)) {
            error_log($content);
            throw new \Exception("Unsplash error: " . $content);
        }

        return $obj;
    }
}