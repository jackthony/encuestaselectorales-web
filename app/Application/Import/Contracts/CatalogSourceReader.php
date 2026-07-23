<?php

namespace App\Application\Import\Contracts;

use App\Application\Import\Data\CatalogImportDocument;

interface CatalogSourceReader
{
    public function read(string $path): CatalogImportDocument;
}
