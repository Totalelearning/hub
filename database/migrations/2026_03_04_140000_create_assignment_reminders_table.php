<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->string('reminder_type', 50);
            $table->date('due_on');
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['user_id', 'learning_module_id', 'reminder_type', 'due_on'], 'assignment_reminder_unique');
            $table->index('status');
            $table->index('due_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_reminders');
    }
};
