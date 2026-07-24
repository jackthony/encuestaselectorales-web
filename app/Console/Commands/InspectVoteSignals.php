<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Models\InteractiveVote;
use App\Infrastructure\Security\AesGcmVotePrivacy;
use Illuminate\Console\Command;

final class InspectVoteSignals extends Command
{
    protected $signature = 'votes:inspect
        {voteId : ULID del voto a inspeccionar}';

    protected $description = 'Muestra en texto plano las señales protegidas de un voto para auditoría interna.';

    public function handle(AesGcmVotePrivacy $privacy): int
    {
        $vote = InteractiveVote::query()->find((string) $this->argument('voteId'));

        if (! $vote) {
            $this->error('No se encontró el voto solicitado.');

            return self::FAILURE;
        }

        $this->info("Voto {$vote->id}");
        $this->line("Ronda: {$vote->survey_round_id}");
        $this->line("Opción: {$vote->survey_option_id}");
        $this->line("Estado: {$vote->status}");
        $this->line("IP (claro): ".$this->decryptField(
            $privacy,
            $vote->ip_ciphertext,
            $vote->ip_nonce,
            $vote->ip_auth_tag,
        ));
        $this->line("IP HMAC: ".(string) $vote->ip_hmac);
        $this->line("Device token (claro): ".$this->decryptField(
            $privacy,
            $vote->device_token_ciphertext,
            $vote->device_token_nonce,
            $vote->device_token_auth_tag,
        ));
        $this->line("Device HMAC: ".(string) $vote->device_token_hmac);
        $this->line("Browser fingerprint (claro): ".$this->decryptField(
            $privacy,
            $vote->browser_fingerprint_ciphertext,
            $vote->browser_fingerprint_nonce,
            $vote->browser_fingerprint_auth_tag,
        ));
        $this->line("Browser HMAC: ".(string) $vote->browser_fingerprint_hmac);

        return self::SUCCESS;
    }

    private function decryptField(
        AesGcmVotePrivacy $privacy,
        mixed $ciphertext,
        mixed $nonce,
        mixed $authTag,
    ): string {
        if (! is_string($ciphertext) || ! is_string($nonce) || ! is_string($authTag)) {
            return '(sin copia reversible)';
        }

        return $privacy->decrypt($ciphertext, $nonce, $authTag);
    }
}
