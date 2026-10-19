<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::first();
echo 'user=' . ($u ? $u->id : 'null') . PHP_EOL;
if ($u) {
    $u->name = $u->name;
    var_export($u->save());
    echo PHP_EOL;
}
