#!/usr/bin/env php
<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Configs;
use LycheeVerify\Verify;
use Illuminate\Support\Facades\DB;

echo "Verifying is_trial logic...\n";

// Get instance
try {
    $verify = resolve(Verify::class);
    echo "Resolved class: " . get_class($verify) . "\n";

    if (!$verify instanceof \App\Services\Verify) {
        echo "ERROR: Verify service is not instance of App\Services\Verify\n";
        exit(1);
    }

    // Insert config if not exists
    $inserted = false;
    if (!Configs::where('key', 'is_trial')->exists()) {
        DB::table('configs')->insert([
            'key' => 'is_trial',
            'value' => '0',
            'cat' => 'lychee SE',
            'type_range' => '0|1',
            'description' => 'Enable trial mode',
            'level' => 1
        ]);
        $inserted = true;
        echo "Inserted temporary config.\n";
    }

    // Test false
    Configs::set('is_trial', 0);
    $val = $verify->is_trial();
    echo "is_trial (0): " . ($val ? 'true' : 'false') . "\n";
    if ($val !== false) {
        echo "ERROR: Expected false\n";
        exit(1);
    }

    // Test true
    Configs::set('is_trial', 1);
    $val = $verify->is_trial();
    echo "is_trial (1): " . ($val ? 'true' : 'false') . "\n";
    if ($val !== true) {
        echo "ERROR: Expected true\n";
        exit(1);
    }

    echo "SUCCESS: is_trial logic verified.\n";

    // Clean up if we inserted it
    if ($inserted) {
        DB::table('configs')->where('key', 'is_trial')->delete();
        echo "Cleaned up temporary config.\n";
    }

} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
