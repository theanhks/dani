<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Throwable;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoload
require __DIR__.'/../vendor/autoload.php';

try {

    /** @var Application $app */
    $app = require __DIR__.'/../bootstrap/app.php';

    // Handle the request
    $response = $app->handleRequest(Request::capture());
    $response->send();

} catch (Throwable $e) {

    // GHI LOG RA STDERR -> Render Logs sẽ nhìn thấy
    error_log("LARAVEL FATAL: ".get_class($e)." - ".$e->getMessage());
    error_log($e->getTraceAsString());

    // Hiển thị lỗi đơn giản ra browser (chỉ GET, HEAD từ Render không thấy)
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');

    echo "============================\n";
    echo "     LARAVEL DEBUG ERROR    \n";
    echo "============================\n\n";
    echo "Class:   ".get_class($e)."\n";
    echo "Message: ".$e->getMessage()."\n\n";
    echo "Check Render → Logs để xem stacktrace đầy đủ.\n";

    exit;
}