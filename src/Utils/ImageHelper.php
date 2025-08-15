<?php

namespace Ollama\Utils;

/**
 * Utilitários para manipulação de imagens
 */
class ImageHelper
{
    /**
     * Codifica uma imagem em base64
     *
     * @param string $imagePath Caminho para o arquivo de imagem
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function encodeImage($imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \InvalidArgumentException('Arquivo de imagem não encontrado: ' . $imagePath);
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            throw new \InvalidArgumentException('Falha ao ler arquivo de imagem: ' . $imagePath);
        }

        return base64_encode($imageData);
    }

    /**
     * Codifica múltiplas imagens em base64
     *
     * @param array $imagePaths Array de caminhos para arquivos de imagem
     * @return array Array de imagens codificadas em base64
     */
    public static function encodeImages(array $imagePaths)
    {
        $encodedImages = array();
        
        foreach ($imagePaths as $imagePath) {
            $encodedImages[] = self::encodeImage($imagePath);
        }
        
        return $encodedImages;
    }

    /**
     * Valida se um arquivo é uma imagem suportada
     *
     * @param string $imagePath
     * @return bool
     */
    public static function isValidImage($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $imagePath);
        finfo_close($finfo);

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Obtém informações sobre uma imagem
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

        return array(
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'mime' => $info['mime'],
            'size' => filesize($imagePath)
        );
    }
}
