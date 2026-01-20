<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Group;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test Group CRUD
echo "--- Testing Group CRUD ---\n";

// 1. Create
echo "[Create] ";
$controller = new \App\Http\Controllers\Api\GroupController();
$request = new Request([
    'name' => 'Test Group',
    'active' => true,
]);
$response = $controller->store($request);
$group = $response->getData(); // Should be a data object or the model
if (isset($group->id)) {
    echo "Success. ID: {$group->id}, Name: {$group->name}\n";
    $groupId = $group->id;
} else {
    echo "Failed. Response: " . json_encode($group) . "\n";
    exit;
}

// 2. Index
echo "[Index] ";
$groups = $controller->index();
echo "Count: " . $groups->count() . "\n";

// 3. Update
echo "[Update] ";
$groupModel = Group::find($groupId);
$updateRequest = new Request([
    'name' => 'Updated Group Name',
]);
$updatedGroup = $controller->update($updateRequest, $groupModel);
echo "New Name: " . $updatedGroup->name . "\n";

// 4. Delete (Soft)
echo "[Delete] ";
$response = $controller->destroy($groupModel);
if ($response->getStatusCode() == 204) {
    echo "Success (204 No Content).\n";
} else {
    echo "Failed. Status: " . $response->getStatusCode() . "\n";
}

// 5. Verify Soft Delete
echo "[Verify Soft Delete] ";
$trashedDto = Group::withTrashed()->find($groupId);
$activeDto = Group::find($groupId);

if ($trashedDto && $trashedDto->trashed() && !$activeDto) {
    echo "Success. Record is soft deleted.\n";
} else {
    echo "Failed. Trashed: " . ($trashedDto ? 'Yes' : 'No') . ", Active: " . ($activeDto ? 'Yes' : 'No') . "\n";
}
