<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.users', function (Blueprint $table) {
            $table->id(); // id int [pk, increment]
            $table->string('username', 300)->unique()->notNullable(); // username varchar(300) unique not null
            $table->string('email', 300)->unique()->notNullable(); // username varchar(300) unique not null
            $table->string('password'); // password varchar not null
            $table->boolean('change_password')->default(false)->notNullable();
            $table->string('token', 50)->default('')->notNullable();
            $table->timestamp('date_change_password')->nullable();
            $table->timestamp('register_date')->notNullable();
            $table->unsignedBigInteger('active')->default(0);
            $table->string('ip', 45)->notNullable(); // ip varchar(45)        

        });

        DB::table('security.users')->insert([
            [
                'username' => 'admin',
                'email' => 'crisclasyt@gmail.com',
                'password' => Hash::make('12345678'),
                'register_date' => now(),
                'active' => 0,
                'ip' => '[IP_ADDRESS]',
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.users');
    }
};
