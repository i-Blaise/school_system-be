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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->date('date');
            
            // Tracking Actions
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            
            // Geolocation Logging (JSON strictly for auditing lat/lng arrays)
            $table->json('clock_in_location')->nullable();
            $table->json('clock_out_location')->nullable();
            
            // Method string (QR Code scan vs Admin override)
            $table->string('clock_in_method')->nullable();
            $table->string('clock_out_method')->nullable();
            
            // Override Tracking and Notes
            $table->foreignUuid('clocked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('clocked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            
            $table->timestamps();

            // Prevent a user from having multiple full attendance slips per day
            $table->unique(['user_id', 'date', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
