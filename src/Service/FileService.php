<?php

namespace App\Service;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FileService
{
    public function __construct(private readonly Filesystem $fileSystem)
    {}

    public function csvConverter(string $filepath, string $delimiter = ',', bool $hasHeader = true)
    {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }

        $decoder = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $context = [
            CsvEncoder::DELIMITER_KEY => $delimiter,
            CsvEncoder::AS_COLLECTION_KEY => true // Even if there is only one line, return an array of array
        ];
        if ($hasHeader === false) {
            $context[CsvEncoder::NO_HEADERS_KEY] = true;
        }
        return $decoder->decode(file_get_contents($filepath), 'csv', $context);
    }

    public function deleteFile(string $path): void
    {
        $this->fileSystem->remove($path);
    }

    public function convertToUTF8(&$input, ?string $fromEncoding = null): void
    {
        if (is_string($input)) {
            $toEncoding = 'UTF-8';
            $fromEncoding = $fromEncoding ?? mb_detect_encoding($input);
            if ($fromEncoding !== $toEncoding) {
                $input = mb_convert_encoding($input, $toEncoding, $fromEncoding);
            }
        } elseif (is_array($input)) {
            foreach ($input as &$value) {
                $this->convertToUTF8($value, $fromEncoding);
            }
            unset($value);
        } elseif (is_object($input)) {
            $vars = array_keys(get_object_vars($input));
            foreach ($vars as $var) {
                $this->convertToUTF8($input->$var, $fromEncoding);
            }
        }
    }
}
