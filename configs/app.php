<?php

return [
    'login' => [
        // TODO: remove images
        'images' => [
            'count' => 8, // remove me.
            'column-count' => 'four', // remove-me. Use semantic-ui class attribute.
        ],
        'retry_count' => 1,
        'phases' => [
            'count' => 2,       // number of phases
            'n' => 3,           // binomial-coefficients {\tbinom {n}{k}}
            'k' => 8,
            'variation' => 1    // From the first nCk coefficients 'n' will use a range between [n - variation, n]
        ],
        'css' => [
            'column-count' => 'four' // Use semantic-ui class attribute.
        ]
    ],
    'signup' => [
        'images-count' => 5,    // number of images the user must remember. Must match with login.
    ],
    'image-fetcher-imp' => '\pictogin\images\unsplash\UnsplashFetcher'
];