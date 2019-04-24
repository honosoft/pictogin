<?php

namespace pictogin\images;

class ImageFactory {
    public static function create() : IImageFetcher {
        global $config;
        return new $config['image-fetcher-imp']();
    }
}