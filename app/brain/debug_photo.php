<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Registro\Entities\Personas;

$persona = Personas::first();
if ($persona) {
    echo "Persona: " . $persona->nombre_completo . "\n";
    echo "User ID: " . $persona->user_id . "\n";
    $path = storage_path('app/public/img/avatars/' . $persona->user_id . '.png');
    echo "Path: " . $path . "\n";
    echo "Exists: " . (File::exists($path) ? 'SI' : 'NO') . "\n";
} else {
    echo "No hay personas\n";
}
