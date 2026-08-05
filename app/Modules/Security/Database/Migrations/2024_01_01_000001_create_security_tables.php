<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tablas sin dependencias
        Schema::create('security.genders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->boolean('active')->default(true);
        });

        Schema::create('security.countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('dial_code', 10);
            $table->string('iso2', 10)->unique();
            $table->boolean('default')->default(false);
        });

        Schema::create('security.document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4);
            $table->string('name', 150);
            $table->text('description');
            $table->boolean('is_natural');
        });

        Schema::create('security.modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 200);
            $table->string('icon', 50);
            $table->integer('order');
        });

        Schema::create('security.codes', function (Blueprint $table) {
            $table->id();
            $table->string('email', 300)->unique();
            $table->string('code');
            $table->timestamp('date');
            $table->boolean('processed')->default(false);
        });

        // 2. security.menus → security.modules
        Schema::create('security.menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 255);
            $table->string('icon', 50);
            $table->integer('order');
            $table->boolean('active');
            $table->unsignedBigInteger('module_id');
            $table->foreign('module_id')->references('id')->on('security.modules');
        });

        // 3. security.processes → security.menus
        Schema::create('security.processes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('description');
            $table->string('icon', 50);
            $table->string('route', 50);
            $table->string('actions', 200);
            $table->integer('order');
            $table->boolean('active')->default(true);
            $table->foreignId('menu_id')->constrained('security.menus')->onDelete('cascade');
        });

        // 4. security.profiles
        Schema::create('security.profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description');
            $table->boolean('active');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('register_date');
            $table->string('ip', 45);
        });

        // 5. security.users (sin profile_id redundante)
        Schema::create('security.users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 300)->unique();
            $table->string('email', 300)->unique();
            $table->string('password');
            $table->boolean('change_password')->default(false);
            $table->string('token', 50)->default('');
            $table->timestamp('date_change_password')->nullable();
            $table->timestamp('register_date');
            $table->unsignedBigInteger('active')->default(0);
            $table->string('ip', 45);
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->string('document', 20)->nullable();
            $table->string('full_name', 300)->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Schema::table('security.users', function (Blueprint $table) {
            $table->foreign('document_type_id')->references('id')->on('security.document_types')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('security.countries')->onDelete('set null');
        });

        // 6. security.permissions → security.processes
        Schema::create('security.permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->foreignId('process_id')
                ->constrained('security.processes')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['process_id', 'slug']);
        });

        // 7. security.profile_permissions → security.profiles, security.permissions
        Schema::create('security.profile_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('security.profiles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('security.permissions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['profile_id', 'permission_id'], 'uniq_profile_permission');
        });

        // 8. security.profiles_users → security.profiles, security.users
        Schema::create('security.profiles_users', function (Blueprint $table) {
            $table->id('id_rol_persona');
            $table->foreignId('id_rol')->constrained('security.profiles')->onDelete('cascade');
            $table->foreignId('id_users')->constrained('security.users')->onDelete('cascade');
            $table->unsignedBigInteger('status')->default(0);
            $table->date('fecha_aprobacion')->nullable();
            $table->unsignedBigInteger('aprobado_por')->nullable();
            $table->unsignedBigInteger('creado_por');
            $table->dateTime('creado_en');
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->dateTime('actualizado_en')->nullable();
        });

        // Sync sequences
        foreach (['security.menus', 'security.processes'] as $table) {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 0) + 1, false)");
        }
    }

    public function down(): void
    {
        $tables = [
            'security.profiles_users',
            'security.profile_permissions',
            'security.permissions',
            'security.users',
            'security.profiles',
            'security.processes',
            'security.menus',
            'security.codes',
            'security.modules',
            'security.document_types',
            'security.countries',
            'security.genders',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
