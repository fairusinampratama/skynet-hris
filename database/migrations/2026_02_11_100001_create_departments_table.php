<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('office');
            $table->boolean('has_shift_schedule')->default(false);
            $table->decimal('office_lat', 10, 8)->default(-7.250445);
            $table->decimal('office_long', 11, 8)->default(112.768845);
            $table->integer('radius_meters')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
