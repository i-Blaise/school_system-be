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
            $table->string('teacher_id')->nullable()->unique()->after('id');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('designation')->nullable()->after('department');
            $table->date('joining_date')->nullable()->after('designation');
            $table->string('phone_country_code')->nullable()->default('+233')->after('phone');
            $table->text('address')->nullable()->after('phone_country_code');
            $table->boolean('medical_condition_alert')->default(false)->after('address');
            $table->string('status')->default('draft')->after('medical_condition_alert');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'teacher_id',
                'department',
                'designation',
                'joining_date',
                'phone_country_code',
                'address',
                'medical_condition_alert',
                'status',
                'created_by',
            ]);
        });
    }
};
