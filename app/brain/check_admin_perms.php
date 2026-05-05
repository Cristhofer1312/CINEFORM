<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \Illuminate\Support\Facades\DB::table('security_profile_permissions')->where('profile_id', 1)->count();
echo "Admin profile_id 1 permissions: $count\n";

if ($count == 0) {
    // Si no tiene, migrar de security_profile_processes a security_profile_permissions temporalmente
    echo "Faltan permisos. Migrando permisos legados temporalmente para no bloquear al admin...\n";
    $legacy = \Illuminate\Support\Facades\DB::table('security_profile_processes')->where('profile_id', 1)->get();
    foreach($legacy as $row) {
        $actions = explode('|', $row->actions);
        foreach($actions as $act) {
            if (empty($act)) continue;
            
            $slug = is_numeric($act) ? \App\Constants\SecurityAction::dbString((int)$act) : $act;
            if (empty($slug) && !is_numeric($act)) $slug = $act;
            
            $perm = \Illuminate\Support\Facades\DB::table('security_permissions')->where('process_id', $row->process_id)->where('slug', $slug)->first();
            $permId = $perm ? $perm->id : null;
            if (!$permId) {
                $permId = \Illuminate\Support\Facades\DB::table('security_permissions')->insertGetId([
                    'process_id' => $row->process_id,
                    'slug' => $slug,
                    'name' => ucwords(str_replace('_', ' ', $slug)),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            try {
                \Illuminate\Support\Facades\DB::table('security_profile_permissions')->insert([
                    'profile_id' => 1,
                    'permission_id' => $permId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {}
        }
    }
    echo "Permisos migrados para admin.\n";
}
