<?php

namespace App\Application\Import\Exceptions;

use RuntimeException;

final class CatalogImportException extends RuntimeException
{
    /**
     * @param  list<string>  $diagnostics
     */
    public function __construct(
        string $message,
        public readonly array $diagnostics = [],
    ) {
        parent::__construct($message);
    }
}
