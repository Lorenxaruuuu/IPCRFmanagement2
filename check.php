<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- EMPLOYEES DISTINCT ROLES ---\n";
$roles = App\Models\Employee::distinct('role')->pluck('role');
foreach ($roles as $r) {
    echo $r . "\n";
}

echo "--- REGISTERED USERS ---\n";
$users = App\Models\User::all();
foreach ($users as $u) {
    echo $u->id . ": " . $u->name . " | Role: " . $u->role . " | Position ID: " . $u->position_id . "\n";
}
