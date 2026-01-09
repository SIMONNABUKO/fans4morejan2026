<?php

require_once 'vendor/autoload.php';

use App\Services\MediaService;
use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing watermark functionality...\n";

try {
    // Create a test image
    $testImagePath = storage_path('app/test_image.png');
    
    // Create a simple test image using GD
    $image = imagecreate(400, 300);
    $bgColor = imagecolorallocate($image, 100, 150, 200);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    imagestring($image, 5, 50, 150, 'Test Image for Watermark', $textColor);
    imagepng($image, $testImagePath);
    imagedestroy($image);
    
    echo "Created test image at: $testImagePath\n";
    
    // Test watermark functionality
    $mediaService = new MediaService();
    $watermarkText = 'fans4more.com/okanya';
    
    echo "Applying watermark: $watermarkText\n";
    
    $watermarkedPath = $mediaService->addImageWatermark($testImagePath, $watermarkText);
    
    echo "Watermark applied successfully!\n";
    echo "Watermarked image saved at: $watermarkedPath\n";
    
    // Check if file exists and has content
    if (file_exists($watermarkedPath)) {
        $fileSize = filesize($watermarkedPath);
        echo "File size: $fileSize bytes\n";
        echo "✅ Watermark test PASSED!\n";
    } else {
        echo "❌ Watermark test FAILED - file not found!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during watermark test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Test completed.\n"; 