<?php

namespace App\Services;

final class PublicPortalData
{
    private string $repoRoot;

    /** @var array<string, mixed> */
    private array $data;

    public function __construct()
    {
        $this->repoRoot = dirname(base_path());

        require_once $this->repoRoot . '/includes/helpers.php';
        require_once $this->repoRoot . '/includes/encuestas.php';

        $data = require $this->repoRoot . '/includes/data.php';
        $this->data = is_array($data) ? $data : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function districts(): array
    {
        return $this->data['distritos'] ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public function candidates(): array
    {
        return $this->data['candidatos'] ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public function parties(): array
    {
        return $this->data['partidos'] ?? [];
    }

    public function districtBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        foreach ($this->districts() as $district) {
            if ((string) ($district['id'] ?? '') === $slug) {
                return $district;
            }
        }

        return null;
    }

    public function partyById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        foreach ($this->parties() as $party) {
            if ((int) ($party['id'] ?? 0) === $id) {
                return $party;
            }
        }

        return null;
    }

    public function candidateById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        foreach ($this->candidates() as $candidate) {
            if ((int) ($candidate['id'] ?? 0) === $id) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function candidatesForDistrict(string $districtId): array
    {
        $districtId = trim($districtId);
        if ($districtId === '') {
            return [];
        }

        $items = [];
        foreach ($this->candidates() as $candidate) {
            if ((string) ($candidate['distritoId'] ?? '') !== $districtId) {
                continue;
            }

            $party = $this->partyById((int) ($candidate['partidoId'] ?? 0));
            $items[] = [
                'id' => (int) ($candidate['id'] ?? 0),
                'nombre' => (string) ($candidate['nombre'] ?? ''),
                'foto' => candidatePhotoSrc($candidate),
                'activo' => (bool) ($candidate['activo'] ?? false),
                'distrito_id' => (string) ($candidate['distritoId'] ?? ''),
                'partido_id' => (int) ($candidate['partidoId'] ?? 0),
                'partido_nombre' => (string) ($party['nombre'] ?? ''),
                'partido_siglas' => (string) ($party['siglas'] ?? ''),
                'partido_logo' => $this->partyLogoSrc($party),
                'partido_color' => partyColorOrGray((int) ($candidate['partidoId'] ?? 0)),
                'partido_initials' => $party ? iniciales((string) ($party['nombre'] ?? '')) : '',
            ];
        }

        return $items;
    }

    public function partyLogoSrc(?array $party): string
    {
        if (!$party) {
            return '';
        }

        foreach (['party_logo_local_path', 'logo', 'link_logo_partido', 'logo_url', 'party_logo_url', 'legacy_party_logo_url'] as $key) {
            $value = $party[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }

    public function activeRoundForDistrict(string $districtId): ?array
    {
        if (!function_exists('getRondaActiva')) {
            return null;
        }

        $round = getRondaActiva($districtId, 'distrito');
        return is_array($round) ? $round : null;
    }

    public function activeRoundForTerritory(string $level, string $slug): ?array
    {
        if (!function_exists('getRondaActiva')) {
            return null;
        }

        $level = strtolower(trim($level));
        if (!in_array($level, ['region', 'provincia'], true)) {
            return null;
        }

        $round = getRondaActiva($slug, $level);
        return is_array($round) ? $round : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function districtsForTerritory(string $level, string $slug): array
    {
        $level = strtolower(trim($level));
        $slug = trim($slug);

        if (!in_array($level, ['region', 'provincia'], true) || $slug === '') {
            return [];
        }

        $items = [];
        foreach ($this->districts() as $district) {
            if ((string) ($district[$level] ?? '') !== $slug) {
                continue;
            }

            $candidates = $this->candidatesForDistrict((string) ($district['id'] ?? ''));
            $items[] = [
                'id' => (string) ($district['id'] ?? ''),
                'nombre' => (string) ($district['nombre'] ?? ''),
                'provincia' => (string) ($district['provincia'] ?? ''),
                'region' => (string) ($district['region'] ?? ''),
                'candidates' => $candidates,
                'candidate_count' => count($candidates),
                'active_round' => $this->activeRoundForDistrict((string) ($district['id'] ?? '')),
                'detail_url' => 'distrito.php?slug=' . rawurlencode((string) ($district['id'] ?? '')),
            ];
        }

        usort($items, static function (array $left, array $right): int {
            return strcasecmp((string) $left['nombre'], (string) $right['nombre']);
        });

        return $items;
    }

    /**
     * @return array{district:?array,candidates:array<int,array<string,mixed>>,activeRound:?array,hasCandidates:bool,voteEnabled:bool,pageTitle:string,pageDescription:string,scopeLabel:string,blockedMessage:?string}
     */
    public function districtPageData(string $slug): array
    {
        $district = $this->districtBySlug($slug);
        $candidates = $district ? $this->candidatesForDistrict((string) ($district['id'] ?? '')) : [];
        $activeRound = $district ? $this->activeRoundForDistrict((string) ($district['id'] ?? '')) : null;
        $hasCandidates = count($candidates) > 0;
        $voteEnabled = $hasCandidates && $activeRound !== null;

        $scopeLabel = $district
            ? trim(
                'Distrito ' . (string) ($district['nombre'] ?? '') .
                ' · Provincia ' . territoryDisplayName((string) ($district['provincia'] ?? '')) .
                ' · Región ' . territoryDisplayName((string) ($district['region'] ?? ''))
            )
            : 'Distrito no encontrado';

        $blockedMessage = null;
        if ($district && !$hasCandidates) {
            $blockedMessage = 'Aún no hay candidatos cargados para este distrito. La encuesta no puede abrirse hasta que exista el padrón definitivo.';
        } elseif ($district && $hasCandidates && !$activeRound) {
            $blockedMessage = 'Ya hay candidatos cargados, pero todavía no existe una ronda activa para este distrito.';
        }

        return [
            'district' => $district,
            'candidates' => $candidates,
            'activeRound' => $activeRound,
            'hasCandidates' => $hasCandidates,
            'voteEnabled' => $voteEnabled,
            'whatsappNumero' => '51971388435',
            'pageTitle' => $district
                ? (($district['nombre'] ?? '') . ' — Distrito | EncuestasElectorales.pe')
                : 'Distrito no encontrado | EncuestasElectorales.pe',
            'pageDescription' => $district
                ? ('Candidatos, partido, ronda activa y voto web del distrito de ' . ($district['nombre'] ?? '') . '.')
                : 'Distrito no encontrado.',
            'scopeLabel' => $scopeLabel,
            'blockedMessage' => $blockedMessage,
            'shareTitle' => $district
                ? ('Distrito ' . ($district['nombre'] ?? '') . ' | EncuestasElectorales.pe')
                : 'Distrito no encontrado | EncuestasElectorales.pe',
            'shareDescription' => $district
                ? 'Candidatos, partido y voto web real del distrito de ' . ($district['nombre'] ?? '') . '.'
                : 'Distrito no encontrado.',
            'shareImage' => $activeRound
                ? $this->sharePreviewPath('assets/img/share/surveys/survey-' . (string) ($activeRound['id'] ?? '') . '.png')
                : $this->sharePreviewPath('assets/img/share/default-share.png'),
            'shareType' => 'article',
        ];
    }

    /**
     * @return array{level:string,slug:string,territoryName:string,districts:array<int,array<string,mixed>>,activeRound:?array,pageTitle:string,pageDescription:string}
     */
    public function territoryPageData(string $level, string $slug): array
    {
        $level = strtolower(trim($level));
        $slug = strtolower(trim($slug));

        $districts = $this->districtsForTerritory($level, $slug);
        $territoryName = $slug !== '' ? territoryDisplayName($slug) : '';
        $activeRound = $this->activeRoundForTerritory($level, $slug);

        $pageTitle = $territoryName !== ''
            ? surveyLevelLabel($level) . ' ' . $territoryName . ' | EncuestasElectorales.pe'
            : 'Encuestas por territorio | EncuestasElectorales.pe';

        $pageDescription = $territoryName !== ''
            ? 'Distritos, candidatos y rondas activas agrupadas por ' . strtolower(surveyLevelLabel($level)) . ' en ' . $territoryName . '.'
            : 'Encuestas por territorio.';

        $groups = [];
        if ($level === 'region') {
            foreach ($districts as $district) {
                $groupKey = (string) ($district['provincia'] ?? '');
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'label' => 'Provincia ' . territoryDisplayName($groupKey),
                        'districts' => [],
                    ];
                }
                $groups[$groupKey]['districts'][] = $district;
            }
        } elseif ($level === 'provincia') {
            $groups[$slug] = [
                'label' => 'Provincia ' . $territoryName,
                'districts' => $districts,
            ];
        }

        if ($groups === [] && $districts !== []) {
            $groups[$slug ?: 'territorio'] = [
                'label' => $territoryName !== '' ? $territoryName : 'Territorio',
                'districts' => $districts,
            ];
        }

        foreach ($groups as &$group) {
            usort($group['districts'], static function (array $left, array $right): int {
                return strcasecmp((string) $left['nombre'], (string) $right['nombre']);
            });
        }
        unset($group);

        return [
            'level' => $level,
            'slug' => $slug,
            'territoryName' => $territoryName,
            'districts' => $districts,
            'groups' => array_values($groups),
            'activeRound' => $activeRound,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'shareTitle' => $pageTitle,
            'shareDescription' => $pageDescription,
            'shareImage' => $activeRound
                ? $this->sharePreviewPath('assets/img/share/surveys/survey-' . (string) ($activeRound['id'] ?? '') . '.png')
                : $this->sharePreviewPath('assets/img/share/default-share.png'),
            'shareType' => 'article',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function fieldStudies(): array
    {
        $studies = [];

        foreach (($this->data['encuestas'] ?? []) as $survey) {
            $encuestadoraId = (string) ($survey['encuestadoraId'] ?? '');
            if ($encuestadoraId === '' || !function_exists('findEncuestadoraById')) {
                continue;
            }

            if (findEncuestadoraById($encuestadoraId) === null) {
                continue;
            }

            $studies[] = $survey;
        }

        return $studies;
    }

    /** @return array<int, array<string, mixed>> */
    public function activeRounds(): array
    {
        $rounds = function_exists('getRondasActivas') ? getRondasActivas() : [];

        return is_array($rounds) ? $rounds : [];
    }

    public function sondeosPageData(): array
    {
        $rondasAbiertas = $this->activeRounds();
        $heroWords = [];

        foreach ($rondasAbiertas as $round) {
            $district = $this->districtBySlug((string) ($round['distrito_id'] ?? ''));
            if ($district && !empty($district['nombre'])) {
                $heroWords[] = (string) $district['nombre'];
            }
        }

        if (count($heroWords) === 0) {
            foreach (array_slice($this->districts(), 0, 4) as $district) {
                if (!empty($district['nombre'])) {
                    $heroWords[] = (string) $district['nombre'];
                }
            }
        }

        $heroWords[] = 'tu distrito';
        $heroWords = array_slice(array_values(array_unique($heroWords)), 0, 4);

        return [
            'pageTitle' => 'EncuestasElectorales.pe - Sondeo en vivo',
            'pageDescription' => 'Sondeos ciudadanos por distrito con validación GPS y bloqueo anti duplicado.',
            'totalDistritos' => count($this->districts()),
            'rondasAbiertas' => $rondasAbiertas,
            'heroWords' => $heroWords,
            'whatsappNumero' => '51971388435',
            'shareTitle' => 'EncuestasElectorales.pe - Sondeo en vivo',
            'shareDescription' => 'Sondeos ciudadanos por distrito con validación GPS y bloqueo anti duplicado.',
            'shareImage' => $this->sharePreviewPath('assets/img/share/home.png'),
            'shareType' => 'website',
        ];
    }

    public function candidatePageData(int $id): array
    {
        $candidate = $this->candidateById($id);
        $party = $candidate ? $this->partyById((int) ($candidate['partidoId'] ?? 0)) : null;
        $district = $candidate ? $this->districtBySlug((string) ($candidate['distritoId'] ?? '')) : null;
        $history = [];

        foreach (($this->data['resultados'] ?? []) as $result) {
            foreach (($result['resultados'] ?? []) as $row) {
                if ((int) ($row['candidatoId'] ?? 0) === $id) {
                    $history[] = [
                        'resultado' => $result,
                        'row' => $row,
                    ];
                }
            }
        }

        return [
            'pageTitle' => $candidate
                ? 'Perfil de ' . ($candidate['nombre'] ?? '') . ' | EncuestasElectorales.pe'
                : 'Candidato no encontrado | EncuestasElectorales.pe',
            'pageDescription' => 'Perfil, partido y trayectoria de sondeos del candidato.',
            'candidate' => $candidate,
            'party' => $party,
            'district' => $district,
            'history' => $history,
            'partyColor' => $candidate ? partyColorOrGray((int) ($candidate['partidoId'] ?? 0)) : '#6b7280',
            'shareTitle' => $candidate
                ? 'Perfil de ' . ($candidate['nombre'] ?? '') . ' | EncuestasElectorales.pe'
                : 'Candidato no encontrado | EncuestasElectorales.pe',
            'shareDescription' => $candidate
                ? 'Perfil del candidato ' . ($candidate['nombre'] ?? '') . ' y su partido en EncuestasElectorales.pe.'
                : 'Perfil de candidato no disponible.',
            'shareImage' => $candidate
                ? $this->sharePreviewPath('assets/img/share/candidates/candidate-' . (string) ($candidate['id'] ?? 0) . '.png')
                : $this->sharePreviewPath('assets/img/share/default-share.png'),
            'shareType' => 'profile',
        ];
    }

    public function surveyPageData(string $id): array
    {
        $survey = null;
        foreach (($this->data['encuestas'] ?? []) as $row) {
            if ((string) ($row['id'] ?? '') === $id) {
                $survey = $row;
                break;
            }
        }

        $result = null;
        $pollster = null;
        $district = null;
        if ($survey) {
            foreach (($this->data['resultados'] ?? []) as $row) {
                if ((string) ($row['encuestaId'] ?? '') === (string) ($survey['id'] ?? '')) {
                    $result = $row;
                    break;
                }
            }
            $pollster = $this->findPollster((string) ($survey['encuestadoraId'] ?? ''));
            $district = $this->districtBySlug((string) ($survey['distritoId'] ?? ''));
        }

        return [
            'pageTitle' => $survey
                ? 'Detalle del estudio — ' . ($pollster['nombre'] ?? '') . ' | EncuestasElectorales.pe'
                : 'Estudio no disponible | EncuestasElectorales.pe',
            'pageDescription' => 'Ficha técnica y resultados de estudios de opinión pública registrados.',
            'survey' => $survey,
            'result' => $result,
            'pollster' => $pollster,
            'district' => $district,
            'candidates' => $this->candidates(),
            'shareTitle' => $survey
                ? ($survey['titulo'] ?? 'Estudio de campo') . ' | EncuestasElectorales.pe'
                : 'Estudio no disponible | EncuestasElectorales.pe',
            'shareDescription' => $survey
                ? 'Resultados y ficha técnica de un estudio real publicado en EncuestasElectorales.pe.'
                : 'Estudio de campo no disponible.',
            'shareImage' => $survey
                ? $this->sharePreviewPath('assets/img/share/surveys/survey-' . (string) ($survey['id'] ?? '') . '.png')
                : $this->sharePreviewPath('assets/img/share/default-share.png'),
            'shareType' => 'article',
        ];
    }

    public function pollstersPageData(): array
    {
        $pollsters = [];
        $studies = $this->fieldStudies();
        $studyCounts = [];

        foreach ($studies as $study) {
            $pollsterId = (string) ($study['encuestadoraId'] ?? '');
            if ($pollsterId === '') {
                continue;
            }
            $studyCounts[$pollsterId] = ($studyCounts[$pollsterId] ?? 0) + 1;
        }

        foreach (($this->data['encuestadoras'] ?? []) as $pollster) {
            $id = (string) ($pollster['id'] ?? '');
            $pollsters[] = [
                'id' => $id,
                'nombre' => (string) ($pollster['nombre'] ?? ''),
                'tipo' => (string) ($pollster['tipo'] ?? ''),
                'web' => (string) ($pollster['web'] ?? ''),
                'study_count' => (int) ($studyCounts[$id] ?? 0),
                'status' => $id === 'propia' ? 'Sondeo web' : 'Registro JNE',
            ];
        }

        return [
            'pageTitle' => 'Directorio de Encuestadoras Registradas JNE - Encuestas Electorales',
            'pageDescription' => 'Directorio oficial de encuestadoras de opinión pública registradas en el JNE para las Elecciones 2026.',
            'pollsters' => $pollsters,
        ];
    }

    private function findPollster(string $id): ?array
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

    private function publicAssetExists(string $relativePath): bool
    {
        $publicRoot = function_exists('public_path')
            ? rtrim(public_path(), DIRECTORY_SEPARATOR)
            : rtrim(base_path('public'), DIRECTORY_SEPARATOR);

        return is_file($publicRoot . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\'));
    }

    private function sharePreviewPath(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');
        $fallback = 'assets/img/share/default-share.png';

        if ($this->publicAssetExists($relativePath)) {
            return $relativePath;
        }

        if ($this->publicAssetExists($fallback)) {
            return $fallback;
        }

        return 'assets/img/default-face.svg';
    }

}
