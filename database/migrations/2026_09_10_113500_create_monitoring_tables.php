<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vendor', 100);
            $table->string('model', 100);
            $table->string('device_type', 100);
            $table->string('management_ip', 45)->unique();
            $table->string('status', 30)->default('unknown')->index();
            $table->boolean('is_polling_enabled')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('device_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('protocol', 30);
            $table->unsignedInteger('port');
            $table->string('username')->nullable();
            $table->text('encrypted_password')->nullable();
            $table->text('encrypted_community')->nullable();
            $table->timestamps();
            $table->unique(['device_id', 'protocol']);
        });

        Schema::create('olt_pon_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot');
            $table->unsignedInteger('port');
            $table->string('name')->nullable();
            $table->string('status', 30)->default('unknown');
            $table->decimal('rx_power_threshold', 8, 3)->nullable();
            $table->timestamps();
            $table->unique(['device_id', 'slot', 'port']);
        });

        Schema::create('onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_pon_port_id')->constrained()->cascadeOnDelete();
            $table->string('onu_identifier', 100);
            $table->string('serial_number', 100)->nullable();
            $table->string('name')->nullable();
            $table->string('status', 30)->default('unknown')->index();
            $table->decimal('rx_power', 8, 3)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['olt_pon_port_id', 'onu_identifier']);
        });

        Schema::create('device_metric_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('metric_name', 100);
            $table->decimal('metric_value', 20, 6);
            $table->timestamp('recorded_at')->index();
            $table->index(['device_id', 'metric_name', 'recorded_at']);
        });

        Schema::create('traffic_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('interface_name');
            $table->decimal('rx_bps', 20, 3);
            $table->decimal('tx_bps', 20, 3);
            $table->timestamp('recorded_at')->index();
            $table->index(['device_id', 'interface_name', 'recorded_at']);
        });

        Schema::create('device_poll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('poll_type', 50);
            $table->string('status', 30)->index();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->index(['device_id', 'poll_type', 'started_at']);
        });

        Schema::create('syslog_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_ip', 45);
            $table->string('facility', 50)->nullable();
            $table->string('severity', 30)->index();
            $table->text('message');
            $table->timestamp('received_at')->index();
            $table->timestamp('parsed_at')->nullable();
            $table->index(['source_ip', 'received_at']);
        });

        Schema::create('cli_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('protocol', 30);
            $table->string('status', 30)->index();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->index(['device_id', 'status']);
        });

        Schema::create('command_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cli_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->text('command');
            $table->text('output_excerpt')->nullable();
            $table->boolean('success');
            $table->timestamp('executed_at')->index();
            $table->index(['device_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_audit_logs');
        Schema::dropIfExists('cli_sessions');
        Schema::dropIfExists('syslog_events');
        Schema::dropIfExists('device_poll_runs');
        Schema::dropIfExists('traffic_samples');
        Schema::dropIfExists('device_metric_samples');
        Schema::dropIfExists('onus');
        Schema::dropIfExists('olt_pon_ports');
        Schema::dropIfExists('device_credentials');
        Schema::dropIfExists('devices');
    }
};
