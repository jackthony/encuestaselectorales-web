<?php

namespace App\Infrastructure\Import;

use App\Application\Import\Data\CatalogImportDocument;
use App\Application\Import\Data\SourceRecord;
use App\Application\Import\Exceptions\CatalogImportException;
use SplFileObject;

final class CsvCatalogReader
{
    public function read(string $path): CatalogImportDocument
    {
        $file = new SplFileObject($path, 'rb');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE);

        $headers = $file->fgetcsv();
        if (! is_array($headers)) {
            throw new CatalogImportException('El CSV no contiene una cabecera válida.');
        }

        $headers = array_map(
            static fn (mixed $header): string => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)),
            $headers,
        );

        if ($headers === [] || in_array('', $headers, true) || count($headers) !== count(array_unique($headers))) {
            throw new CatalogImportException('El CSV contiene cabeceras vacías o duplicadas.');
        }

        $records = [];
        $versions = [];
        $rowNumber = 1;

        while (! $file->eof()) {
            $rowNumber++;
            $values = $file->fgetcsv();
            if ($values === false || $values === [null] || $this->isBlank($values)) {
                continue;
            }

            if (count($values) > count($headers)) {
                throw new CatalogImportException("La fila {$rowNumber} tiene más columnas que la cabecera.");
            }

            $values = array_pad($values, count($headers), null);
            /** @var array<string, mixed> $record */
            $record = array_combine($headers, array_slice($values, 0, count($headers)));
            $records[] = new SourceRecord($rowNumber, $record);
            $versions[] = trim((string) ($record['schema_version'] ?? $record['Versión Esquema'] ?? '1.0'));
        }

        return $this->document($path, $records, $versions);
    }

    /**
     * @param  list<mixed>  $values
     */
    private function isBlank(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<SourceRecord>  $records
     * @param  list<string>  $versions
     */
    private function document(string $path, array $records, array $versions): CatalogImportDocument
    {
        $versions = array_values(array_unique(array_filter($versions)));
        $version = $versions[0] ?? '1.0';

        if (count($versions) > 1 || $version !== '1.0') {
            throw new CatalogImportException('El CSV usa una versión de esquema no soportada.', $versions);
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
