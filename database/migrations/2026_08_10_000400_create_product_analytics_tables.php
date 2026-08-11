<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visit_days', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_hash', 64);
            $table->date('visited_on');
            $table->unsignedInteger('page_views')->default(1);
            $table->string('last_path', 255)->nullable();
            $table->timestamps();
            $table->unique(['visitor_hash', 'visited_on']);
            $table->index('visited_on');
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type');
            $table->string('channel', 40);
            $table->char('recipient_hash', 64)->nullable();
            $table->foreignId('earthquake_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('site_visit_days');
    }
};
