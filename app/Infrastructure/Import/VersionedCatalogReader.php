<?php

namespace App\Infrastructure\Import;

use App\Application\Import\Contracts\CatalogSourceReader;
use App\Application\Import\Data\CatalogImportDocument;
use App\Application\Import\Exceptions\CatalogImportException;

final readonly class VersionedCatalogReader implements CatalogSourceReader
{
    public function __construct(
        private CsvCatalogReader $csv,
        private JsonCatalogReader $json,
    ) {}

    public function read(string $path): CatalogImportDocument
    {
        $resolved = realpath($path);
        if ($resolved === false || ! is_file($resolved) || ! is_readable($resolved)) {
            throw new CatalogImportException('No se puede leer el archivo electoral indicado.');
        }

        return match (strtolower(pathinfo($resolved, PATHINFO_EXTENSION))) {
            'csv' => $this->csv->read($resolved),
            'json' => $this->json->read($resolved),
            default => throw new CatalogImportException('Formato no soportado. Use un archivo CSV o JSON.'),
        };
    }
}
