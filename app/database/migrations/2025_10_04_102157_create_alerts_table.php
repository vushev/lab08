<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('measurement_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->text('message');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at'], 'alerts_user_created_idx');
            $table->index(['device_id', 'created_at'], 'alerts_device_created_idx');
            $table->index('status', 'alerts_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
