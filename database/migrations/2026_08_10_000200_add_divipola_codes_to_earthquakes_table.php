<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('earthquakes', function (Blueprint $table) {
            $table->string('municipality_code', 5)->nullable()->index()->after('municipality');
            $table->string('department_code', 2)->nullable()->index()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('earthquakes', function (Blueprint $table) {
            $table->dropColumn(['municipality_code', 'department_code']);
        });
    }
};
