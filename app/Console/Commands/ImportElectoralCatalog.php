<?php

namespace App\Console\Commands;

use App\Application\Import\Data\CatalogImportOptions;
use App\Application\Import\Exceptions\CatalogImportException;
use App\Infrastructure\Import\TransactionalElectoralCatalogImporter;
use Illuminate\Console\Command;

final class ImportElectoralCatalog extends Command
{
    protected $signature = 'elections:import-candidacies
        {path : Ruta al CSV o JSON aprobado}
        {--source-cycle= : Ciclo electoral, por ejemplo ERM2026}
        {--source-system=JNE : Sistema que entregó los datos}
        {--operator= : Identificador no sensible del operador}
        {--publish : Publica territorios solo si el lote no tiene rechazos}
        {--dry-run : Valida y resume sin escribir en la base de datos}';

    protected $description = 'Importa el catálogo electoral CSV/JSON de forma idempotente y auditable.';

    public function handle(TransactionalElectoralCatalogImporter $importer): int
    {
        try {
            $summary = $importer->import(
                (string) $this->argument('path'),
                new CatalogImportOptions(
                    sourceCycle: $this->stringOption('source-cycle'),
                    sourceSystem: (string) $this->option('source-system'),
                    operatorIdentifier: $this->stringOption('operator'),
                    publish: (bool) $this->option('publish'),
                    dryRun: (bool) $this->option('dry-run'),
                ),
            );
        } catch (CatalogImportException $exception) {
            $this->error($exception->getMessage());
            foreach ($exception->diagnostics as $diagnostic) {
                $this->line(' - '.$diagnostic);
            }

            return self::FAILURE;
        }

        $this->info($summary->dryRun ? 'Validación completada sin escrituras.' : 'Importación completada.');
        $this->table(
            ['Lote', 'Territorio', 'Nivel', 'Cargo', 'Estado', 'Creadas', 'Actualizadas', 'Sin cambios', 'Rechazadas'],
            array_map(static fn (array $run): array => [
                $run['run_id'] ?? 'dry-run',
                $run['territory'],
                $run['scope_type'],
                $run['office_type'],
                $run['idempotent'] ? 'idéntico' : $run['status'],
                $run['created_rows'],
                $run['updated_rows'],
                $run['unchanged_rows'],
                $run['rejected_rows'],
            ], $summary->runs),
        );

        $this->line('Checksum: '.$summary->checksum);
        $this->line("Total: {$summary->totalRows}; rechazadas: {$summary->rejectedRows}.");

        return self::SUCCESS;
    }

    private function stringOption(string $name): ?string
    {
        $value = trim((string) $this->option($name));

        return $value === '' ? null : $value;
    }
}
