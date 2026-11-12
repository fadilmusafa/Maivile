<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * One-time load of the demo data in database/dumbs/maivile.sql.
 *
 * Only the INSERT lines are replayed (the schema comes from the other
 * migrations), and `migrations`/`sessions` are skipped. Existing rows in the
 * tables that the dump fills are replaced.
 */
return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('dumbs/maivile.sql');

        if (! File::exists($path)) {
            return;
        }

        $inserts = [];
        foreach (preg_split('/\R/', File::get($path)) as $line) {
            if (preg_match('/^INSERT INTO `([^`]+)`/', $line, $m)
                && ! in_array($m[1], ['migrations', 'sessions'], true)) {
                $inserts[$m[1]][] = $line;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($inserts as $table => $statements) {
                DB::table($table)->truncate();
                foreach ($statements as $statement) {
                    DB::unprepared($statement);
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        //
    }
};
