<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

use App\Models\Procurement;
use App\Models\RawMaterial;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\ProcurementController;
use Illuminate\Support\Facades\Auth;

// Mock Auth - Login as Procurement User
$user = \App\Models\User::whereHas('role', function ($q) {
    $q->where('name', 'Procurement');
})->first();

if (!$user) {
    echo "Error: No Procurement user found. Trying to create one or find any user and assign role (simulation)...\n";
    // Fallback: find any user and force role (mocking object, not DB save to avoid side effects if possible, but easier to just find one)
    // Actually, let's just pick the first user and Mock the isProcurement method? No, difficult in script.
    // Let's assume there is one, or pick ID 1 and hope.
    // Better:
    $role = \App\Models\Role::where('name', 'Procurement')->first();
    if ($role) {
        $user = \App\Models\User::where('role_id', $role->id)->first();
    }
}

if (!$user) {
    echo "Error: Could not find a Procurement user.\n";
    exit(1);
}
Auth::login($user);

// Get Section and Materials
$section = Section::where('name', 'like', '%Lounge%')->first();
if (!$section) {
    // If no "Lounge", try "Bar" or "Club" or just take the second one
    $section = Section::skip(1)->first();
}

$materials = RawMaterial::take(2)->get();

if (!$section || $materials->count() < 2) {
    echo "Error: Need Section 'Lounge' (or alternative) and at least 2 Raw Materials.\n";
    exit(1);
}

echo "Testing for Section: " . $section->name . " (ID: " . $section->id . ")\n";

$data = [
    'supplier_id' => 'Test Supplier',
    'section_id' => $section->id, // Force Section ID
    'purchase_date' => now()->format('Y-m-d'),
    'items' => [
        [
            'raw_material_id' => $materials[0]->id,
            'quantity' => 10,
            'unit_cost' => 100,
            'expiry_date' => '', // Test empty string
        ],
        [
            'raw_material_id' => $materials[1]->id, // Use DIFFERENT material
            'quantity' => 5,
            'unit_cost' => 50,
            'expiry_date' => '', // Test empty string
        ]
    ]
];

echo "Request Data:\n";
print_r($data);

// Create Request
$request = Request::create('/api/v1/procurements', 'POST', $data);
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Create Controller
$controller = app(ProcurementController::class);

try {
    $response = $controller->store($request);
    echo "Success! \n";
    // Check if response is JSON or object
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        print_r($response->getData(true));
    } else {
        echo "Response is not JSON.\n";
    }
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Error: \n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
