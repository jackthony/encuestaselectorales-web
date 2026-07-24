<?php

namespace App\Http\Controllers\Api;

use App\Application\Data\RegisterVoteData;
use App\Application\Vote\RegisterVote;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Vote\Exceptions\DuplicateVote;
use App\Domain\Vote\Exceptions\GeographicValidationFailed;
use App\Domain\Vote\Exceptions\VoteUnavailable;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterVoteRequest;
use App\Infrastructure\Security\TrustedClientIp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Cookie;

final class VoteController extends Controller
{
    public function __construct(
        private readonly RegisterVote $registerVote,
        private readonly SurveyRoundQuery $rounds,
        private readonly TrustedClientIp $clientIp,
    ) {}

    public function store(RegisterVoteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $deviceToken = $this->deviceToken($request, $validated['device_token'] ?? null);
        $clientIp = $this->resolveClientIp($request);
        if ($clientIp instanceof JsonResponse) {
            return $clientIp;
        }

        try {
            $vote = $this->registerVote->execute(new RegisterVoteData(
                surveyRoundId: $validated['survey_round_id'],
                surveyOptionId: $validated['survey_option_id'],
                latitude: (float) $validated['gps_latitude'],
                longitude: (float) $validated['gps_longitude'],
                accuracyMeters: (float) $validated['gps_accuracy_meters'],
                interactionTimeMs: (int) $validated['interaction_time_ms'],
                deviceToken: $deviceToken,
                browserFingerprint: $validated['browser_fingerprint'],
                clientIp: $clientIp,
            ));
        } catch (DuplicateVote) {
            return $this->error(
                409,
                'duplicate_vote',
                'Ya registramos un voto para esta encuesta desde esta conexión o dispositivo.',
            );
        } catch (VoteUnavailable $exception) {
            return $this->error(
                409,
                $exception->getMessage(),
                'Esta encuesta no está disponible para votar.',
            );
        } catch (GeographicValidationFailed) {
            return $this->error(
                422,
                'geographic_validation_failed',
                'No pudimos validar tu ubicación dentro del ámbito de esta encuesta.',
            );
        }

        $vote->loadMissing('surveyRound');
        $roundResult = $this->rounds->forTerritory((string) $vote->surveyRound->territory_id);

        return response()->json([
            'status' => 'success',
            'code' => 'vote_registered',
            'message' => 'Voto registrado correctamente.',
            'device_token' => $deviceToken,
            'data' => [
                'vote_id' => (string) $vote->getKey(),
                'result' => $roundResult->toArray(),
            ],
        ], 201)->cookie($this->deviceCookie($deviceToken, $request->isSecure()));
    }

    private function deviceToken(Request $request, mixed $submitted): string
    {
        $token = is_string($submitted) ? trim($submitted) : '';
        if ($token === '') {
            $token = trim((string) $request->cookie('encuestas_device', ''));
        }

        return strlen($token) >= 32 ? $token : bin2hex(random_bytes(32));
    }

    private function resolveClientIp(Request $request): string|JsonResponse
    {
        try {
            return $this->clientIp->resolve($request);
        } catch (RuntimeException) {
            return $this->error(
                503,
                'network_validation_failed',
                'No pudimos validar la conexión de forma segura. Inténtalo nuevamente.',
            );
        }
    }

    private function deviceCookie(string $token, bool $secure): Cookie
    {
        return cookie(
            'encuestas_device',
            $token,
            60 * 24 * 365,
            '/',
            null,
            $secure,
            true,
            false,
            'Lax',
        );
    }

    private function error(int $status, string $code, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ], $status);
    }
}
