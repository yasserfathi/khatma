<?php
try {
    $indices = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM users');
    $output = "";
    foreach ($indices as $index) {
        $output .= "Index: " . $index->Key_name . " - Column: " . $index->Column_name . "\n";
    }
    file_put_contents('c:\laragon\www\khatma\indices.txt', $output);
} catch (\Exception $e) {
    file_put_contents('c:\laragon\www\khatma\indices.txt', "Error: " . $e->getMessage());
}
