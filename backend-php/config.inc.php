<?php

/* note that all _URL and _DIR configurations below must end with a forward slash (/) */

$config = array(

    /* Url for image serving in final download */
    'SERVE_URL' => 'http://localhost/mosaico/media/',

    /* Base path for image serving in final download */
    'SERVE_PATH' => $_SERVER['DOCUMENT_ROOT'] . '/mosaico/media/',

    /* Local file system path to the static images folder (relative to BASE_PATH) */
    'STATIC_DIR' => 'static/',

    /* Base Url for accessing Mosaico */
    'BASE_URL' => 'http://localhost/mosaico/',

    /* Local file system base path to where image directories are located */
    'BASE_PATH' => $_SERVER['DOCUMENT_ROOT'] . '/mosaico/',

    /* Dir name for the uploads folder (relative to BASE_URL and BASE_PATH) */
    'UPLOADS_DIR' => 'uploads/',

    /* Dir name for the thumbnail images folder (relative to BASE_URL and BASE_PATH) */
    'THUMBNAILS_DIR' => 'uploads/thumb/',

    /* width and height of generated thumbnails */
    'THUMBNAIL_WIDTH' => 90,
    'THUMBNAIL_HEIGHT' => 90
);
