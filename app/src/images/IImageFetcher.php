<?php

namespace pictogin\images;

/**
 * Act as a factory as well by the static method create()
 */
interface IImageFetcher {
    /**
     * Will fetch the data from the server.
     * @param int $count Number fo items to fetch
     * @return array of items [id, url]
     */
    public function fetch(int $count) : array;

    /**
     * @param string $id
     * @return array item [id, url]
     */
    public function find(string $id) : array;
}