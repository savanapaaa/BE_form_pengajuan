<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Bootstrap Laravel without full application
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $attachment = App\Models\Attachment::first();
    if ($attachment) {
        echo "Attachment found: " . $attachment->id . "\n";
        echo "Thumbnail URL: " . ($attachment->thumbnail_url ?? 'null') . "\n";
        echo "To JSON: " . json_encode($attachment->toArray()) . "\n";
    } else {
        echo "No attachments found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
