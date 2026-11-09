<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('request_date');
            $table->date('planned_date')->nullable();
            $table->date('planned_return_date')->nullable();
            $table->date('actual_handover_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->text('description')->nullable();
            $table->string('approval_status', 20)->default('pending');
            $table->string('process_status', 30)->default('menunggu_verifikasi');
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'process_status']);
            $table->index(['service_type_id', 'approval_status']);
            $table->index('request_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
