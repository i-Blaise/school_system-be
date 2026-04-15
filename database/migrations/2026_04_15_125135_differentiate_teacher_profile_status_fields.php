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
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->renameColumn('status', 'registration_status');
        });

        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('status')->default('Active')->after('employment_status')->comment('Active, Inactive, Leave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->renameColumn('registration_status', 'status');
        });
    }
};
