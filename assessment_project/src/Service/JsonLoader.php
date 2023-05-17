<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class JsonLoader
 * Handles loading a JSON file from a filepath
 *
 * @package App\Service
 */
class JsonLoader
{   
    /**
     * @var array Decoded JSON data
     */
    private $data;

    /**
     * JsonLoader constructor.
     *
     * @param string $filePath The path to the JSON file.
     * @throws FileNotFoundException If the file does not exist at the provided path.
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $json = file_get_contents($filePath);
        $this->data = json_decode($json, true);
    }

    /**
     * Returns the loaded JSON data as an associative array
     *
     * @return array The loaded JSON data
     */
    public function getData(): array
    {
        return $this->data;
    }
}
