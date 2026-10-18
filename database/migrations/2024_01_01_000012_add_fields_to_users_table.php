<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Relasi
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->after('company_id')->constrained('departments')->onDelete('set null');

            // Profil pekerja
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
            $table->enum('gender', ['L', 'P'])->nullable()->after('position');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('education_level', 10)->nullable()->after('birth_date');
            $table->date('work_started_at')->nullable()->after('education_level');

            // Preference
            $table->string('locale', 5)->default('id')->after('work_started_at');
            $table->boolean('is_active')->default(true)->after('locale');

            $table->index('company_id');
            $table->index('department_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'company_id',
                'department_id',
                'phone',
                'position',
                'gender',
                'birth_date',
                'education_level',
                'work_started_at',
                'locale',
                'is_active',
            ]);
        });
    }
};
