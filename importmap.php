<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper
 * - "entrypoint" (true/false) is set for the main files
 * - "url" is used for cdn entries
 *
 * @return array
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
];
