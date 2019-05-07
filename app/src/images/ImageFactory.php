<?php

namespace pictogin\images;

use pictogin\Pictogin;

class ImageFactory{
    /** @var IImageFetcher */
    private static $imageFetcher;

    public static function instance() : IImageFetcher {
        if (!static::$imageFetcher) {
            $config = Pictogin::config();
            $class = $config['image-fetcher-imp'];
            static::$imageFetcher =  new $class();
        }

        return static::$imageFetcher;
    }

    /**
     * Will fetch the data from the server.
     * @param int $count Number fo items to fetch
     * @return array of items [id, url]
     */
    public static function fetch(int $count): array {
        return static::instance()->fetch($count);
    }

    /**
     * @param string $id
     * @return array item [id, url]
     */
    public static function find(string $id): array {
        return static::instance()->find($id);
    }
}