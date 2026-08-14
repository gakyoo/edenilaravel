<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('preferred_date');
            $table->time('preferred_time');
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending'); // pending, confirmed, completed, cancelled
            $table->timestamps();
        });

        // Track tour requests per property (mirrors views/enquiries/favorites counters)
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('tours_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('tours_count');
        });
        Schema::dropIfExists('tours');
    }
};
