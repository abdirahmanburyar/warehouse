<?php
// Simple cache clearing script
echo "Clearing Laravel cache...\n";

// Clear config cache
if (file_exists('bootstrap/cache/config.php')) {
    unlink('bootstrap/cache/config.php');
    echo "Config cache cleared.\n";
}

// Clear route cache
if (file_exists('bootstrap/cache/routes-v7.php')) {
    unlink('bootstrap/cache/routes-v7.php');
    echo "Route cache cleared.\n";
}

// Clear view cache
$viewCacheDir = 'storage/framework/views';
if (is_dir($viewCacheDir)) {
    $files = glob($viewCacheDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "View cache cleared.\n";
}

echo "Cache clearing completed.\n";
