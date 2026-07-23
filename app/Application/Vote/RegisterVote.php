<?php

namespace App\Application\Vote;

use App\Application\Data\RegisterVoteData;
use App\Domain\Survey\PublicationState;
use App\Domain\Survey\RoundAvailability;
use App\Domain\Vote\Contracts\GeographicValidator;
use App\Domain\Vote\Contracts\VotePrivacy;
use App\Domain\Vote\Exceptions\DuplicateVote;
use App\Domain\Vote\Exceptions\GeographicValidationFailed;
use App\Domain\Vote\Exceptions\VoteUnavailable;
use App\Domain\Vote\VoteType;
use App\Infrastructure\Persistence\Models\InteractiveVote;
use App\Infrastructure\Persistence\Models\SurveyOption;
use App\Infrastructure\Persistence\Models\SurveyRound;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class RegisterVote
{
    public function __construct(
        private readonly VotePrivacy $privacy,
        private readonly GeographicValidator $geography,
    ) {}

    public function execute(RegisterVoteData $data): InteractiveVote
    {
        return DB::transaction(function () use ($data): InteractiveVote {
            $round = SurveyRound::query()
                ->with('territory')
                ->lockForUpdate()
                ->find($data->surveyRoundId);

            if (! $round
                || $round->publication_state !== PublicationState::Published
                || $round->readiness_state !== RoundAvailability::Active
                || now()->lt($round->opens_at)
                || now()->gt($round->closes_at)
            ) {
                throw new VoteUnavailable('round_unavailable');
            }

            $option = SurveyOption::query()
                ->whereKey($data->surveyOptionId)
                ->where('survey_round_id', $round->getKey())
                ->where('status', 'eligible')
                ->whereHas('candidacy', fn ($query) => $query
                    ->where('status', 'active')
                    ->where('territory_id', $round->territory_id)
                    ->where('office_type', $round->office_type)
                    ->where('election_cycle', $round->election_cycle)
                    ->whereHas('candidate', fn ($candidate) => $candidate->where('status', 'active'))
                    ->whereHas('politicalParty', fn ($party) => $party->where('status', 'active')))
                ->lockForUpdate()
                ->first();

            if (! $option) {
                throw new VoteUnavailable('option_unavailable');
            }

            if (! $this->geography->contains(
                $round->territory,
                $data->latitude,
                $data->longitude,
                $data->accuracyMeters,
            )) {
                throw new GeographicValidationFailed('location_outside_scope');
            }

            $signals = $this->privacy->protect(
                $data->clientIp,
                $data->deviceToken,
                $data->browserFingerprint,
            );

            try {
                return InteractiveVote::query()->create([
                    'survey_round_id' => $round->getKey(),
                    'survey_option_id' => $option->getKey(),
                    'validated_territory_id' => $round->territory_id,
                    'vote_type' => VoteType::Candidate,
                    'gps_latitude' => $data->latitude,
                    'gps_longitude' => $data->longitude,
                    'gps_accuracy_meters' => $data->accuracyMeters,
                    'geo_validation_method' => 'configured_bounds',
                    'geo_validation_result' => 'inside',
                    'interaction_time_ms' => $data->interactionTimeMs,
                    'ip_ciphertext' => $signals->ipCiphertext,
                    'ip_nonce' => $signals->ipNonce,
                    'ip_auth_tag' => $signals->ipAuthTag,
                    'ip_encryption_key_version' => $signals->encryptionKeyVersion,
                    'ip_hmac' => $signals->ipHmac,
                    'ip_hmac_key_version' => $signals->ipHmacKeyVersion,
                    'device_token_hmac' => $signals->deviceHmac,
                    'device_hmac_key_version' => $signals->deviceHmacKeyVersion,
                    'browser_fingerprint_hmac' => $signals->browserHmac,
                    'browser_hmac_key_version' => $signals->browserHmacKeyVersion,
                    'status' => 'accepted',
                ]);
            } catch (QueryException $exception) {
                if (in_array((string) $exception->getCode(), ['23000', '19'], true)) {
                    throw new DuplicateVote('duplicate_vote', previous: $exception);
                }

                throw $exception;
            }
        }, attempts: 3);
    }
}
