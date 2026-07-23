<?php

namespace App\Application\Import\Contracts;

use App\Application\Import\Data\CatalogImportOptions;
use App\Application\Import\Data\CatalogImportSummary;

interface ElectoralCatalogImporter
{
    public function import(string $path, CatalogImportOptions $options): CatalogImportSummary;
}
