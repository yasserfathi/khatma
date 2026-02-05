<?php
try {
    $indices = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM users');
    foreach ($indices as $index) {
        echo "Index: " . $index->Key_name . " - Column: " . $index->Column_name . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
