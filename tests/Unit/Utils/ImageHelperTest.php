<?php

use Vluzrmos\Ollama\Utils\ImageHelper;

class ImageHelperTest extends TestCase
{
    private $tempImagePath;

    public function setUp()
    {
        // Create a temporary test image file
        $this->tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_image.jpg';
        
        // Create a simple 1x1 pixel JPEG image data
        $imageData = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD//gATQ3JlYXRlZCB3aXRoIEdJTVD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');
        file_put_contents($this->tempImagePath, $imageData);
    }

    public function tearDown()
    {
        if (file_exists($this->tempImagePath)) {
            unlink($this->tempImagePath);
        }
    }

    public function testEncodeImageSuccess()
    {
        $encoded = ImageHelper::encodeImage($this->tempImagePath);
        
        $this->assertTrue(is_string($encoded));
        $this->assertNotEmpty($encoded);
        
        // Verify it's valid base64
        $decoded = base64_decode($encoded, true);
        $this->assertNotFalse($decoded);
    }

    public function testEncodeImageFileNotFound()
    {
        $nonExistentPath = '/path/to/nonexistent/image.jpg';
        
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Image file not found: ' . $nonExistentPath);
        
        ImageHelper::encodeImage($nonExistentPath);
    }

    public function testEncodeImagesMultiple()
    {
        $imagePaths = [$this->tempImagePath, $this->tempImagePath];
        
        $encodedImages = ImageHelper::encodeImages($imagePaths);
        
        $this->assertTrue(is_array($encodedImages));
        $this->assertCount(2, $encodedImages);
        
        foreach ($encodedImages as $encoded) {
            $this->assertTrue(is_string($encoded));
            $this->assertNotEmpty($encoded);
            
            // Verify it's valid base64
            $decoded = base64_decode($encoded, true);
            $this->assertNotFalse($decoded);
        }
    }

    public function testEncodeImagesEmpty()
    {
        $encodedImages = ImageHelper::encodeImages([]);
        
        $this->assertTrue(is_array($encodedImages));
        $this->assertEmpty($encodedImages);
    }

    public function testEncodeImageUrl()
    {
        $encodedUrl = ImageHelper::encodeImageUrl($this->tempImagePath);
        
        $this->assertTrue(is_string($encodedUrl));
        $this->assertStringStartsWith('data:image/jpeg;base64,', $encodedUrl);
        
        // Extract and verify the base64 part
        $base64Part = substr($encodedUrl, strlen('data:image/jpeg;base64,'));
        $decoded = base64_decode($base64Part, true);
        $this->assertNotFalse($decoded);
    }

    public function testEncodeImageUrlFileNotFound()
    {
        $nonExistentPath = '/path/to/nonexistent/image.jpg';
        
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Image file not found: ' . $nonExistentPath);
        
        ImageHelper::encodeImageUrl($nonExistentPath);
    }

    public function testEncodeImagesUrl()
    {
        $imagePaths = [$this->tempImagePath, $this->tempImagePath];
        
        $encodedUrls = ImageHelper::encodeImagesUrl($imagePaths);
        
        $this->assertTrue(is_array($encodedUrls));
        $this->assertCount(2, $encodedUrls);
        
        foreach ($encodedUrls as $encodedUrl) {
            $this->assertTrue(is_string($encodedUrl));
            $this->assertStringStartsWith('data:image/jpeg;base64,', $encodedUrl);
        }
    }

    public function testIsValidImageTrue()
    {
        $isValid = ImageHelper::isValidImage($this->tempImagePath);
        
        $this->assertTrue($isValid);
    }

    public function testIsValidImageFalse()
    {
        $isValid = ImageHelper::isValidImage('/path/to/nonexistent/image.jpg');
        
        $this->assertFalse($isValid);
    }

    public function testGetImageInfo()
    {
        $info = ImageHelper::getImageInfo($this->tempImagePath);
        
        $this->assertTrue(is_array($info));
        $this->assertArrayHasKeys(['width', 'height', 'type', 'mime', 'size'], $info);
        
        $this->assertEquals(1, $info['width']);
        $this->assertEquals(1, $info['height']);
        $this->assertEquals(IMAGETYPE_JPEG, $info['type']);
        $this->assertEquals('image/jpeg', $info['mime']);
        $this->assertTrue(is_int($info['size']));
        $this->assertGreaterThan(0, $info['size']);
    }

    public function testGetImageInfoFileNotFound()
    {
        $info = ImageHelper::getImageInfo('/path/to/nonexistent/image.jpg');
        
        $this->assertFalse($info);
    }

    public function testEncodeImageUrlInvalidImage()
    {
        // Create a text file instead of image
        $textPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_text.txt';
        file_put_contents($textPath, 'not an image');
        
        try {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage('Could not get image information: ' . $textPath);
            
            ImageHelper::encodeImageUrl($textPath);
        } finally {
            if (file_exists($textPath)) {
                unlink($textPath);
            }
        }
    }
}
