<?php
/**
 * Clear OPcache and restart
 */

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!<br>";
} else {
    echo "OPcache not available.<br>";
}

// Clear realpath cache
clearstatcache(true);
echo "Realpath cache cleared!<br>";

echo "<br><strong>Cache cleared! Please refresh the page you were trying to access.</strong><br>";
echo "<br><a href='pages/customers/edit.php?id=1'>Test Edit Customer Page</a><br>";
echo "<a href='pages/dashboard.php'>Go to Dashboard</a>";
