<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'agent', 'buyer', 'seller', 'tenant'])->default('buyer')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('company_name')->nullable()->after('phone');
            $table->string('license_number')->nullable()->after('company_name');
            $table->boolean('license_verified')->default(false)->after('license_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'company_name', 'license_number', 'license_verified']);
        });
    }
};
