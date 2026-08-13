<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            // Identification
            $table->string('parcel_number')->nullable();          // APN / parcel number
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('address_line')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();                  // TZ: Dar es Salaam, Arusha, etc.
            $table->string('country')->default('Tanzania');
            $table->string('postal_code')->nullable();
            $table->text('legal_description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('property_type', ['residential', 'commercial', 'industrial', 'land', 'mixed_use'])->default('residential');
            $table->enum('listing_type', ['sale', 'rent', 'both'])->default('sale');

            // Physical characteristics
            $table->decimal('lot_size', 12, 2)->nullable();        // sq m (TZ uses sq m)
            $table->decimal('building_area', 12, 2)->nullable();   // sq m
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('stories')->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->string('construction_type')->nullable();
            $table->string('roof_type')->nullable();
            $table->unsignedSmallInteger('roof_age_years')->nullable();
            $table->unsignedSmallInteger('parking_spaces')->nullable();
            $table->string('parking_type')->nullable();
            $table->string('zoning_classification')->nullable();
            $table->json('amenities')->nullable();                 // e.g. ["security", "water_tank", "generator", "gym"]

            // Financial
            $table->decimal('price', 15, 2)->nullable();           // TZS or USD (see currency)
            $table->string('currency', 3)->default('TZS');
            $table->decimal('market_value', 15, 2)->nullable();
            $table->decimal('tax_value', 15, 2)->nullable();
            $table->decimal('hoa_fees', 12, 2)->nullable();
            $table->decimal('rental_income', 12, 2)->nullable();
            $table->boolean('is_negotiable')->default(true);

            // Status
            $table->enum('status', ['active', 'pending', 'sold', 'off_market', 'rented'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('enquiries_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'property_type', 'listing_type']);
            $table->index(['city', 'region']);
            $table->index(['price', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
