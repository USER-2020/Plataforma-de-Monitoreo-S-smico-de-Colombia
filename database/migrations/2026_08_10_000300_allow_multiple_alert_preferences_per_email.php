<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('earthquake_subscribers', function (Blueprint $table) {
            $table->string('preference_key', 64)->nullable()->after('department');
        });

        DB::table('earthquake_subscribers')->orderBy('id')->get()->each(function ($subscriber) {
            $magnitude = number_format((float) $subscriber->min_magnitude, 1, '.', '');
            $department = mb_strtolower(trim((string) $subscriber->department));
            $key = hash('sha256', mb_strtolower(trim($subscriber->email))."|{$magnitude}|{$department}");

            DB::table('earthquake_subscribers')->where('id', $subscriber->id)->update(['preference_key' => $key]);
        });

        Schema::table('earthquake_subscribers', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique('preference_key');
        });
    }

    public function down(): void
    {
        Schema::table('earthquake_subscribers', function (Blueprint $table) {
            $table->dropUnique(['preference_key']);
            $table->dropColumn('preference_key');
        });
    }
};
