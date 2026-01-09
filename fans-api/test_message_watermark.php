<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Services\MessageService;
use App\Services\VaultService;
use App\Services\PermissionService;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Intervention Image v3 text watermarking with font path selection...\n";

try {
    // Get the first user and set a new watermark
    $user = User::first();
    if (!$user) {
        echo "No users found in database\n";
        exit(1);
    }
    
    $user->update(['media_watermark' => 'FONT WATERMARK TEST']);
    echo "User watermark set to: {$user->media_watermark}\n";
    
    // Create a new test image with a yellow background
    $testImagePath = storage_path('app/test_image_yellow.png');
    if (!file_exists($testImagePath)) {
        $image = imagecreate(400, 400);
        $bgColor = imagecolorallocate($image, 255, 255, 0); // Yellow background
        $textColor = imagecolorallocate($image, 0, 0, 0); // Black text
        imagestring($image, 5, 80, 190, 'Yellow BG', $textColor);
        imagepng($image, $testImagePath);
        imagedestroy($image);
        echo "Created test image at: {$testImagePath}\n";
    }
    
    // Create an UploadedFile instance
    $uploadedFile = new UploadedFile(
        $testImagePath,
        'test_image_yellow.png',
        'image/png',
        null,
        true
    );
    
    // Test the MessageService
    $messageService = new MessageService(
        new \App\Repositories\MessageRepository(),
        new VaultService(),
        new PermissionService(),
        new MediaService()
    );
    
    echo "Testing MessageService storeMedia method...\n";
    
    // Use reflection to access the protected method
    $reflection = new ReflectionClass($messageService);
    $storeMediaMethod = $reflection->getMethod('storeMedia');
    $storeMediaMethod->setAccessible(true);
    
    // Set the authenticated user
    auth()->login($user);
    
    $result = $storeMediaMethod->invoke($messageService, $uploadedFile, 'test');
    
    echo "Media processing result: {$result}\n";
    echo "Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 