<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4);
            $table->string('name', 150);
            $table->text('description');
            $table->boolean('is_natural');
            
        });

        DB::table('security.document_types')->insert([
            [
                'code' => 'V',
                'name' => 'Venezolano',
                'description' => 'Venezolano',
                'is_natural' => true,
            ],
            [
                'code' => 'E',
                'name' => 'Extranjero',
                'description' => 'Extranjero',
                'is_natural' => true,
            ],
            [
                'code' => 'P',
                'name' => 'Pasaporte',
                'description' => 'Pasaporte',
                'is_natural' => true,
            ],
            [
                'code' => 'V',
                'name' => 'Firma Personal',
                'description' => 'Firma Personal',
                'is_natural' => false,
            ],
            [
                'code' => 'J',
                'name' => 'Jurídico',
                'description' => 'Jurídico',
                'is_natural' => false,
            ],
            [
                'code' => 'G',
                'name' => 'Gobierno',
                'description' => 'Gobierno',
                'is_natural' => false,
            ],
            [
                'code' => 'C',
                'name' => 'Comuna',
                'description' => 'Comuna',
                'is_natural' => false,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.document_types');
    }
};
