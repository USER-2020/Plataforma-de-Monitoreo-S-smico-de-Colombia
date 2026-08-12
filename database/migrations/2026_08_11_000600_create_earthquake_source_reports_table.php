<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earthquake_source_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('earthquake_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('external_id');
            $table->decimal('magnitude', 4, 2)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('depth_km', 8, 2)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->text('source_url')->nullable();
            $table->json('raw_data');
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index(['earthquake_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earthquake_source_reports');
    }
};
