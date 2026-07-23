<?php

namespace App\Infrastructure\Import;

use App\Application\Import\Data\CatalogImportDocument;
use App\Application\Import\Data\SourceRecord;
use App\Application\Import\Exceptions\CatalogImportException;
use JsonException;

final class JsonCatalogReader
{
    public function read(string $path): CatalogImportDocument
    {
        try {
            $decoded = json_decode(
                preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($path)),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new CatalogImportException('El JSON electoral no es válido: '.$exception->getMessage());
        }

        if (! is_array($decoded)) {
            throw new CatalogImportException('El JSON electoral debe contener una lista o un objeto con candidacies.');
        }

        $meta = isset($decoded['meta']) && is_array($decoded['meta']) ? $decoded['meta'] : [];
        $rows = isset($decoded['candidacies']) && is_array($decoded['candidacies'])
            ? $decoded['candidacies']
            : (array_is_list($decoded) ? $decoded : null);

        if ($rows === null) {
            throw new CatalogImportException('El JSON electoral no contiene la colección candidacies.');
        }

        $version = trim((string) ($meta['schema_version'] ?? $rows[0]['schema_version'] ?? '1.0'));
        if ($version !== '1.0') {
            throw new CatalogImportException("La versión JSON {$version} no está soportada.");
        }

        $records = [];
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                throw new CatalogImportException('El JSON contiene una fila que no es un objeto.', ['row '.($index + 1)]);
            }
            $records[] = new SourceRecord($index + 1, $row);
        }

        return new CatalogImportDocument(
            path: $path,
            checksum: hash_file('sha256', $path),
            sizeBytes: (int) filesize($path),
            schemaVersion: $version,
            mappingVersion: 'electoral-catalog-v2',
            records: $records,
        );
    }
}
