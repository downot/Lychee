<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('configs')
            ->where('key', 'is_trial')
            ->update(['level' => 0]);
    }

    public function down(): void
    {
        DB::table('configs')
            ->where('key', 'is_trial')
            ->update(['level' => 1]);
    }
};
