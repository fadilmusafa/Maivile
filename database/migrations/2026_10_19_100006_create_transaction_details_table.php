<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('legalization_doc_type', 50)->nullable();
            $table->unsignedSmallInteger('copies')->nullable();
            $table->time('pickup_time_start')->nullable();
            $table->time('pickup_time_end')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_relation', 50)->nullable();
            $table->string('receiver_id_number', 50)->nullable();
            $table->text('handover_note')->nullable();
            $table->text('return_condition_note')->nullable();
            $table->foreignId('handover_proof_file_id')->nullable()->constrained('transaction_files')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
