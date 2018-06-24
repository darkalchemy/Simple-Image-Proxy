<?php

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/image', function (Request $request, Response $response) {
    $keys = $this->get('settings')['keys'];
    $proxy = new ImageProxy\DecryptUrl($keys);
    $image = $proxy->decrypt_url($request);
    $response->write($image);

    return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE);
});

$app->run();
