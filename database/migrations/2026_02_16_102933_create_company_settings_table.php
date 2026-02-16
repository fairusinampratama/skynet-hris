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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('office_name')->default('Main Office');
            $table->text('office_address')->nullable();
            $table->decimal('office_lat', 10, 8)->default(-7.250445);
            $table->decimal('office_long', 11, 8)->default(112.768845);
            $table->integer('radius_meters')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
