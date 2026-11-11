<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('current_number')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
