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
            $table->string('logo_path')->nullable();
            $table->text('office_address')->nullable();
            $table->decimal('office_lat', 10, 8)->default(-7.250445);
            $table->decimal('office_long', 11, 8)->default(112.768845);
            $table->integer('radius_meters')->default(100);
            
            // Payroll Settings
            $table->unsignedInteger('transport_allowance')->default(400000);
            $table->unsignedInteger('meal_allowance')->default(500000);
            $table->unsignedInteger('late_fine_per_day')->default(50000);
            
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
