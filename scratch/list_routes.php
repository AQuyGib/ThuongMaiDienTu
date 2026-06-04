<?php
require 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/vendor/autoload.php';
$app = require_once 'd:/repogist/ThuongMaiDienTu/thuongmaidientu/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routeCollection = Route::getRoutes();
foreach ($routeCollection as $value) {
    echo $value->methods()[0] . " | " . $value->uri() . " | " . $value->getName() . "\n";
}
