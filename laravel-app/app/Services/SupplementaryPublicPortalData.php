<?php

namespace App\Services;

final class SupplementaryPublicPortalData
{
    private string $repoRoot;

    /** @var array<string, mixed> */
    private array $data;

    public function __construct(private readonly PublicPortalData $publicPortalData)
    {
        $this->repoRoot = dirname(base_path());

        require_once $this->repoRoot . '/includes/helpers.php';
        require_once $this->repoRoot . '/includes/encuestas.php';

        $data = require $this->repoRoot . '/includes/data.php';
        $this->data = is_array($data) ? $data : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function pollsters(): array
    {
        $counts = [];
        foreach ($this->fieldStudies() as $survey) {
            $id = (string) ($survey['encuestadoraId'] ?? '');
            if ($id === '') {
                continue;
            }
            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        $items = [];
        foreach (($this->data['encuestadoras'] ?? []) as $pollster) {
            $id = (string) ($pollster['id'] ?? '');
            $items[] = [
                'id' => $id,
                'nombre' => (string) ($pollster['nombre'] ?? ''),
                'tipo' => (string) ($pollster['tipo'] ?? ''),
                'web' => (string) ($pollster['web'] ?? ''),
                'active_studies' => (int) ($counts[$id] ?? 0),
                'initials' => iniciales((string) ($pollster['nombre'] ?? '')),
                'status_label' => (string) ($pollster['tipo'] ?? '') === 'propia' ? 'Propia' : 'Registrada',
            ];
        }

        usort($items, static fn (array $left, array $right): int => strcasecmp($left['nombre'], $right['nombre']));

        return $items;
    }

    /** @return array<int, array<string, mixed>> */
    public function fieldStudies(): array
    {
        $studies = [];

        foreach (($this->data['encuestas'] ?? []) as $survey) {
            $encuestadoraId = (string) ($survey['encuestadoraId'] ?? '');
            if ($encuestadoraId === '') {
                continue;
            }

            if ($this->pollsterById($encuestadoraId) === null) {
                continue;
            }

            $studies[] = $survey;
        }

        return $studies;
    }

    public function pollsterById(string $id): ?array
    {
        $id = trim($id);
        if ($id === '') {
            return null;
        }

        foreach (($this->data['encuestadoras'] ?? []) as $pollster) {
            if ((string) ($pollster['id'] ?? '') === $id) {
                return $pollster;
            }
        }

        return null;
    }

    public function surveyById(string $id): ?array
    {
        $id = trim($id);
        if ($id === '') {
            return null;
        }

        foreach (($this->data['encuestas'] ?? []) as $survey) {
            if ((string) ($survey['id'] ?? '') === $id) {
                return $survey;
            }
        }

        return null;
    }

    public function surveyResultBySurveyId(string $id): ?array
    {
        $id = trim($id);
        if ($id === '') {
            return null;
        }

        foreach (($this->data['resultados'] ?? []) as $result) {
            if ((string) ($result['encuestaId'] ?? '') === $id) {
                return $result;
            }
        }

        return null;
    }

    public function candidatePageData(int $candidateId): array
    {
        $candidate = $this->publicPortalData->candidateById($candidateId);
        $district = $candidate ? $this->publicPortalData->districtBySlug((string) ($candidate['distritoId'] ?? '')) : null;
        $party = $candidate ? $this->publicPortalData->partyById((int) ($candidate['partidoId'] ?? 0)) : null;
        $relatedCandidates = $district ? $this->publicPortalData->candidatesForDistrict((string) ($district['id'] ?? '')) : [];
        $activeRound = $district ? $this->publicPortalData->activeRoundForDistrict((string) ($district['id'] ?? '')) : null;

        $history = [];
        foreach (($this->data['resultados'] ?? []) as $result) {
            foreach (($result['resultados'] ?? []) as $row) {
                if ((int) ($row['candidatoId'] ?? 0) === $candidateId) {
                    $history[] = [
                        'survey' => $result,
                        'value' => $row,
                    ];
                }
            }
        }

        return [
            'candidate' => $candidate,
            'district' => $district,
            'party' => $party,
            'partyColor' => $candidate ? partyColorOrGray((int) ($candidate['partidoId'] ?? 0)) : '#6b7280',
            'relatedCandidates' => $relatedCandidates,
            'activeRound' => $activeRound,
            'history' => $history,
            'pageTitle' => $candidate
                ? ('Perfil de ' . ($candidate['nombre'] ?? '') . ' | EncuestasElectorales.pe')
                : 'Candidato no encontrado | EncuestasElectorales.pe',
            'pageDescription' => $candidate
                ? ('Perfil del candidato ' . ($candidate['nombre'] ?? '') . ' con partido, distrito y estado de publicación.')
                : 'Candidato no encontrado.',
        ];
    }

    public function surveyPageData(string $surveyId): array
    {
        $survey = $this->surveyById($surveyId);
        $result = $survey ? $this->surveyResultBySurveyId((string) ($survey['id'] ?? '')) : null;
        $pollster = $survey ? $this->pollsterById((string) ($survey['encuestadoraId'] ?? '')) : null;

        $rows = [];
        foreach (($result['resultados'] ?? []) as $row) {
            $candidate = $this->publicPortalData->candidateById((int) ($row['candidatoId'] ?? 0));
            if (!$candidate) {
                continue;
            }

            $party = $this->publicPortalData->partyById((int) ($candidate['partidoId'] ?? 0));
            $rows[] = [
                'candidate' => $candidate,
                'party' => $party,
                'percentage' => (float) ($row['porcentaje'] ?? 0),
                'color' => partyColorOrGray((int) ($candidate['partidoId'] ?? 0)),
            ];
        }

        return [
            'survey' => $survey,
            'pollster' => $pollster,
            'result' => $result,
            'rows' => $rows,
            'pageTitle' => $survey
                ? ('Estudio — ' . ($pollster['nombre'] ?? 'Encuesta') . ' | EncuestasElectorales.pe')
                : 'Estudio no disponible | EncuestasElectorales.pe',
            'pageDescription' => $survey
                ? 'Ficha técnica y resultados del estudio de opinión pública.'
                : 'Estudio no disponible.',
        ];
    }

    public function encuestadorasPageData(): array
    {
        return [
            'pollsters' => $this->pollsters(),
            'pageTitle' => 'Directorio de encuestadoras | EncuestasElectorales.pe',
            'pageDescription' => 'Directorio de encuestadoras registradas y estudios visibles en la plataforma.',
        ];
    }

    public function sondeosPageData(): array
    {
        $activeRounds = $this->publicPortalData->activeRounds();
        $heroWords = [];

        foreach ($activeRounds as $round) {
            $district = $this->publicPortalData->districtBySlug((string) ($round['distrito_id'] ?? ''));
            if ($district && !empty($district['nombre'])) {
                $heroWords[] = (string) $district['nombre'];
            }
        }

        if (count($heroWords) === 0) {
            foreach (array_slice($this->publicPortalData->districts(), 0, 4) as $district) {
                if (!empty($district['nombre'])) {
                    $heroWords[] = (string) $district['nombre'];
                }
            }
        }

        $heroWords[] = 'tu distrito';
        $heroWords = array_slice(array_values(array_unique($heroWords)), 0, 4);

        return [
            'activeRounds' => $activeRounds,
            'rondasAbiertas' => $activeRounds,
            'fieldStudies' => $this->fieldStudies(),
            'districts' => $this->publicPortalData->districts(),
            'totalDistritos' => count($this->publicPortalData->districts()),
            'heroWords' => $heroWords,
            'whatsappNumero' => '51971388435',
            'pageTitle' => 'Sondeos activos | EncuestasElectorales.pe',
            'pageDescription' => 'Sondeos ciudadanos en vivo y estudios de campo publicados.',
        ];
    }
}
