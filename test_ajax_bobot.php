<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate HTTP request
$request = Illuminate\Http\Request::create('/bobot-penilaian', 'GET');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

try {
    $controller = new App\Http\Controllers\BobotPenilaianController(
        new App\Services\BobotPenilaianService()
    );
    
    $response = $controller->index($request);
    
    // Get response content
    if (method_exists($response, 'getData')) {
        $data = $response->getData();
        echo "Response type: DataTables\n";
        echo "Data count: " . (isset($data->data) ? count($data->data) : 0) . "\n";
        if (isset($data->data) && count($data->data) > 0) {
            echo "\nFirst row sample:\n";
            print_r($data->data[0]);
        }
        if (isset($data->error)) {
            echo "\nError: " . $data->error . "\n";
        }
    } else {
        echo "Response: " . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
