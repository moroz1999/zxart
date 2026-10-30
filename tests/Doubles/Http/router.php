<?php

// What LocalFileServer serves to the download tests.
// Types go out as written, without a charset PHP would add to them.
ini_set('default_charset', '');
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
    case '/page-1251.txt':
        header('Content-Type: text/plain; charset=windows-1251');
        echo mb_convert_encoding('Привет, Спектрум', 'Windows-1251', 'UTF-8');
        break;
    case '/meta-1251.html':
        header('Content-Type: text/html');
        echo mb_convert_encoding('<html><head><meta charset="windows-1251"></head><body>Игра</body></html>', 'Windows-1251', 'UTF-8');
        break;
    case '/long.txt':
        header('Content-Type: text/plain; charset=utf-8');
        echo str_repeat('ж', 3000);
        break;
    case '/missing.html':
        http_response_code(404);
        header('Content-Type: text/html');
        echo '<p>No such page</p>';
        break;
    case '/redirect-page':
        header('Location: /page.html', true, 301);
        echo 'moved';
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
