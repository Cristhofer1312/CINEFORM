<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('security.genders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->boolean('active')->default(true);
        });
        DB::table('security.genders')->insert([
            [
                "name" => "Masculino",
            ],
            [
                "name" => "Femenino",
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('security.genders');
    }
};
