<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earthquakes', function (Blueprint $table) {
            $table->id();
            $table->string('external_id');
            $table->string('source', 30);
            $table->decimal('magnitude', 4, 2)->index();
            $table->string('magnitude_type', 20)->nullable();
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->decimal('depth_km', 8, 2)->nullable();
            $table->string('place');
            $table->string('municipality')->nullable();
            $table->string('department')->nullable()->index();
            $table->string('country')->default('Colombia');
            $table->timestamp('occurred_at')->index();
            $table->text('source_url')->nullable();
            $table->json('raw_data');
            $table->timestamps();
            $table->unique(['external_id', 'source']);
        });
        Schema::create('earthquake_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status');
            $table->unsignedInteger('events_received')->default(0);
            $table->unsignedInteger('events_created')->default(0);
            $table->unsignedInteger('events_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earthquake_sync_logs');
        Schema::dropIfExists('earthquakes');
    }
};
