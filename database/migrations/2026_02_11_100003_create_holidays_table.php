<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->date('date');
            $table->string('name');
            $table->string('type')->default('national_holiday');
            $table->timestamps();
            
            $table->unique(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
