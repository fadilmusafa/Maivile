<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'registration_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Akun lama (dibuat Admin / seeder) otomatis berstatus approved.
            $table->string('registration_status', 20)->default('approved')->index(); // pending | approved | rejected
            $table->text('registration_note')->nullable();
            $table->timestamp('registration_reviewed_at')->nullable();
            $table->foreignId('registration_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'registration_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registration_reviewed_by');
            $table->dropColumn(['registration_status', 'registration_note', 'registration_reviewed_at']);
        });
    }
};
