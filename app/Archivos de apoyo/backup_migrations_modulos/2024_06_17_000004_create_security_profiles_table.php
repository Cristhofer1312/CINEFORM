<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('security.profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description');
            $table->boolean('active');            
            $table->unsignedBigInteger('user_id');// Creado por user_id bigint [not null]
            $table->timestamp('register_date');
             $table->string('ip', 45); // ip varchar(45) [not null]
        });

        DB::table('security.profiles')->insert([
            [
                'name' => 'Administrator',
                'description' => 'Administrator',
                'active' => true,
                'user_id' => 1,
                'register_date'=>now(),
                'ip' => '127.0.0.1',
            ],

            [
                'name' => 'Facilitador',
                'description' => 'Facilitador',
                'active' => true,
                'user_id' => 2,
                'register_date'=>now(),
                'ip' => '127.0.0.1',
            ],
            [
                'name' => 'Participante',
                'description' => 'Participante',
                'active' => true,
                'user_id' => 3,
                'register_date'=>now(),
                'ip' => '127.0.0.1',
            ],
            [
                'name' => 'Coordinador',
                'description' => 'Coordinador',
                'active' => true,
                'user_id' => 3, 
                'register_date'=>now(),
                'ip' => '127.0.0.1',
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('security.profiles');
    }
};
