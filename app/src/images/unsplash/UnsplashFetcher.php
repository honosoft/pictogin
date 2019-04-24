<?php

namespace pictogin\images\unsplash;

use pictogin\images\IImageFetcher;

class UnsplashFetcher implements IImageFetcher {

    /** @var array file config */
    private $config;

    public function __construct() {
        $this->config = require_once(getcwd() . '/../configs/unsplash.php');
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
        $url = "https://api.unsplash.com/$action?client_id=" . $this->config['accessKey'];
        if ($params) {
            $url .= "&$params";
        }
        $opts = [
            'http'=>[
              'method' => "GET",
              'header' => "Accept: application/json\r\n" . 
                          "Accept-Version: v1\r\n",
                          "Authorization: Client-ID " . $this->config['accessKey'] . "\r\n" // For some reason it doesn't work. Maybe the documentation is wrong. Added in query parameter.
            ]
        ];
        $context = stream_context_create($opts);
        $content = file_get_contents($url, false, $context);
        return json_decode($content);
    }
}