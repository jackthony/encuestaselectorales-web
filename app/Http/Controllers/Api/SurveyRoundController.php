<?php

namespace App\Http\Controllers\Api;

use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoundResultResource;
use App\Http\Resources\SurveyRoundResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SurveyRoundController extends Controller
{
    public function __construct(private readonly SurveyRoundQuery $rounds) {}

    public function index(): AnonymousResourceCollection
    {
        return SurveyRoundResource::collection($this->rounds->activeNational());
    }

    public function territory(string $territory): RoundResultResource
    {
        return new RoundResultResource($this->rounds->forTerritory($territory));
    }
}
