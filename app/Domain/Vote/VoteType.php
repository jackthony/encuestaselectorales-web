<?php

namespace App\Domain\Vote;

enum VoteType: string
{
    case Candidate = 'candidate';
    case Blank = 'blank';
    case Invalid = 'invalid';
}
