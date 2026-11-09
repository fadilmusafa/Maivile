<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('document_number', 100)->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('document_kind', 20)->default('physical');
            $table->string('availability', 20)->default('tersedia');
            $table->string('physical_location', 100)->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_mime', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_user_id', 'category_id']);
            $table->index('availability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
