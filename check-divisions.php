<?php

require_once 'bootstrap/app.php';

$app = $app ?: require_once 'bootstrap/app.php';

// Boot the application
$app->boot();

$divisions = \App\Models\Division::all();

echo "Divisions found: " . $divisions->count() . "\n";

foreach ($divisions as $division) {
    echo $division->id . ': ' . $division->name . ' (slug: ' . $division->slug . ')' . "\n";
}