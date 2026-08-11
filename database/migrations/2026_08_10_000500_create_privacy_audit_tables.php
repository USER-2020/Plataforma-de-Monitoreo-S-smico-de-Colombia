<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_cookies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('service');
            $table->string('purpose');
            $table->boolean('required')->default(false);
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->string('provider')->default('terracosismos');
            $table->text('description');
            $table->timestamps();
        });

        $now = now();
        DB::table('system_cookies')->insert([
            ['name' => 'terracosismos_consent', 'service' => 'Klaro', 'purpose' => 'Preferencias de privacidad', 'required' => true, 'duration_days' => 365, 'provider' => 'terracosismos', 'description' => 'Conserva las decisiones de consentimiento.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'laravel_session', 'service' => 'Aplicación', 'purpose' => 'Sesión y seguridad', 'required' => true, 'duration_days' => null, 'provider' => 'terracosismos', 'description' => 'Mantiene una sesión segura durante la navegación.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'XSRF-TOKEN', 'service' => 'Aplicación', 'purpose' => 'Seguridad CSRF', 'required' => true, 'duration_days' => null, 'provider' => 'terracosismos', 'description' => 'Protege formularios y solicitudes contra falsificación.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'terracosismos_audit', 'service' => 'Auditoría', 'purpose' => 'Evidencia de consentimiento', 'required' => true, 'duration_days' => 365, 'provider' => 'terracosismos', 'description' => 'Relaciona de forma anónima las decisiones de privacidad.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'terracosismos_visitor', 'service' => 'Analítica', 'purpose' => 'Métricas de visitas', 'required' => false, 'duration_days' => 365, 'provider' => 'terracosismos', 'description' => 'Identificador anónimo habilitado únicamente con consentimiento.', 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('consent_audits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_hash', 64);
            $table->char('ip_hash', 64);
            $table->text('ip_address')->nullable();
            $table->string('config_version', 30);
            $table->json('consents');
            $table->string('action', 30);
            $table->string('path', 255)->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
            $table->index(['visitor_hash', 'consented_at']);
        });

        Schema::create('user_action_audits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_hash', 64);
            $table->char('session_hash', 64);
            $table->char('ip_hash', 64);
            $table->text('ip_address')->nullable();
            $table->string('action', 80);
            $table->string('path', 255);
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['action', 'occurred_at']);
            $table->index(['visitor_hash', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_action_audits');
        Schema::dropIfExists('consent_audits');
        Schema::dropIfExists('system_cookies');
    }
};
