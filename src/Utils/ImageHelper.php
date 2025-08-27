<?php

namespace Vluzrmos\Ollama\Utils;

/**
 * Image manipulation utilities
 */
class ImageHelper
{
    /**
     * Encodes an image to base64
     *
     * @param string $imagePath Path to the image file
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function encodeImage($imagePath)
    {
        if (!is_file($imagePath)) {
            throw new \InvalidArgumentException('Image file not found: ' . $imagePath);
        }

        if (!is_readable($imagePath)) {
            throw new \InvalidArgumentException('Image file is not readable: ' . $imagePath);
        }

        $imageData = file_get_contents($imagePath);

        if ($imageData === false) {
            throw new \InvalidArgumentException('Failed to read image file: ' . $imagePath);
        }
        
        return base64_encode($imageData);
    }

    /**
     * Encodes multiple images to base64
     *
     * @param array $imagePaths Array of image file paths
     * @return array Array of base64 encoded images
     */
    public static function encodeImages(array $imagePaths)
    {
        $encodedImages = [];
        
        foreach ($imagePaths as $imagePath) {
            $encodedImages[] = self::encodeImage($imagePath);
        }
        
        return $encodedImages;
    }

    public static function encodeImageUrl($imagePath)
    {
        $data = static::encodeImage($imagePath);
        $info = static::getImageInfo($imagePath);

        if ($info === false) {
            throw new \InvalidArgumentException('Could not get image information: ' . $imagePath);
        }

        return 'data:' . $info['mime'] . ';base64,' . $data;
    }

    public static function encodeImagesUrl(array $imagesPaths)
    {
        $encodedImages = [];

        foreach ($imagesPaths as $imagePath) {
            $encodedImages[] = static::encodeImageUrl($imagePath);
        }

        return $encodedImages;
    }
    /**
     * Validates if a file is a supported image
     *
     * @param string $imagePath
     * @return bool
     */
    public static function isValidImage($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        return true;
    }



    /**
     * Gets information about an image
     *
     * @param string $imagePath
     * @return array|false
     */
    public static function getImageInfo($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $info = getimagesize($imagePath);
        if ($info === false) {
            return false;
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'mime' => $info['mime'],
            'size' => filesize($imagePath)
        ];
    }
}
