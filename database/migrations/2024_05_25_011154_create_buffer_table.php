<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buffer', function (Blueprint $table) {
            $table->id();
            $table->boolean('import_status');
            $table->boolean('validate_status');
            $table->integer('row_no');
            $table->text('message');
            $table->text('data');
            $table->integer('document_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buffer');
    }
};
