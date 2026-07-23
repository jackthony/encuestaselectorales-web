<?php

namespace App\Domain\Survey;

enum PublicationState: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
