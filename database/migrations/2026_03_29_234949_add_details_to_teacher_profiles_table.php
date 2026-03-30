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
            $table->string('phone')->nullable()->after('date_of_birth');
            $table->string('twitter')->nullable()->after('phone');
            $table->string('linkedin')->nullable()->after('twitter');
            $table->string('facebook')->nullable()->after('linkedin');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropColumn(['phone', 'twitter', 'linkedin', 'facebook']);
        });
    }
};
