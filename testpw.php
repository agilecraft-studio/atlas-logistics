<?php
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(__DIR__.'/.env');
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Model\User;
$users = User::all();
foreach ($users as $u) {
    $v = password_verify('Malaw1@2026', $u->password) ? 'MATCH' : 'no-match';
    echo $u->email.' | '.$v.PHP_EOL;
}
