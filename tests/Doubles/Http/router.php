<?php

// What LocalFileServer serves to the download tests.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$port = $_SERVER['SERVER_PORT'];
switch ($path) {
    case '/file.bin':
        header('Content-Type: application/octet-stream');
        echo str_repeat('a', 1000);
        break;
    case '/big.bin':
        header('Content-Type: application/octet-stream');
        echo str_repeat('b', 5000);
        break;
    case '/image.png':
        header('Content-Type: image/png');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        break;
    case '/intro.tap':
        header('Content-Type: application/octet-stream');
        echo "\x13\x00\x00\x03INTRO     " . str_repeat("\x00\xff", 64);
        break;
    case '/page.html':
        header('Content-Type: text/html');
        echo '<!doctype html><html><head><title>Page</title></head><body><p>Not a picture</p></body></html>';
        break;
    case '/redirect':
        header('Location: /file.bin', true, 302);
        break;
    case '/redirect-image':
        header('Location: /image.png', true, 302);
        break;
    case '/redirect-away':
        header("Location: http://refused.test:$port/file.bin", true, 302);
        break;
    case '/loop':
        header('Location: /loop', true, 302);
        break;
    case '/host':
        echo $_SERVER['HTTP_HOST'];
        break;
    default:
        http_response_code(404);
}
