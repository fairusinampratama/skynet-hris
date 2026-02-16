<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
            // Check-In Data
            $table->time('check_in_time')->nullable();
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_long', 11, 8)->nullable();
            $table->integer('check_in_accuracy')->nullable(); // meters
            $table->string('check_in_photo_path')->nullable();
            $table->string('device_fingerprint')->nullable();
            
            // Check-Out Data
            $table->time('check_out_time')->nullable();
            $table->text('work_summary')->nullable(); // Technicians only
            
            // Flags & Anomalies
            $table->boolean('is_late')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id', 'date']); // One attendance per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
