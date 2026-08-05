<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.profile_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('security.processes')->onDelete('cascade');
            $table->foreignId('profile_id')->constrained('security.profiles')->onDelete('cascade');
            $table->string('actions', 200)->nullable();
            /*  $table->foreign('process_id')->references('id')->on('security.processes');
             $table->foreign('profile_id')->references('id')->on('security.profiles'); */
        });

        $sql = "
                INSERT INTO security.profile_processes(process_id, profile_id, actions)
                SELECT id, 1, actions
                FROM security.processes
                ";
        DB::insert($sql);

        /*
        DB::table('security_profile_processes')->insert([

             [
                 'process_id' => '4',
                 'profile_id' => '4',
                 'actions' => '*',
             ]
         ]);
         */

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.profile_processes');
    }
};
