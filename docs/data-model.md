# Data model — encuestaselectorales-web

> Technical entities: shape + reference + priority, all in one place. `docs/backlog.md` connects these entities to concrete features; this doc is the "what is each object," not the "when does it get built."

## Entity index

| # | Entity | Reference | Priority |
|---|---|---|---|
| 1 | District | `encuestas.com.pe` nav (43 districts, see `design-references.md`) | MVP |
| 2 | Province/Region | simulatuvoto's `regiones.ts` (27 regions) + JNE/INEI ubigeo | Phase 2 |
| 3 | Political party | simulatuvoto's `partidos.ts` (pattern), real data = JNE municipal lists | MVP |
| 4 | Candidate | simulatuvoto's `Candidato` interface (`src/lib/types.ts`) | MVP |
| 5 | Judicial records/flags | simulatuvoto's `CandidatoFlags`/`SentenciaPenalDetalle` | Phase 2 |
| 6 | Pollster | `design-references.md` (IEP, Ipsos, Datum, CPI + own) | MVP |
| 7 | Poll (round) | simulatuvoto's `001_encuesta_respuestas.sql` migration (stratification enums) + `METODOLOGIA_ENCUESTAS_ELECTORALES.md` | MVP |
| 8 | Result per candidate | `resultados-lima.json` pattern (below) | MVP |
| 9 | Fact sheet | G1's "Metodologia" pattern + IEP/CPI/Datum/Ipsos PDFs | MVP |
| 10 | Multi-source comparison | G1 (filter by institute) / 538 (polling average) | MVP |
| 11 | Individual response (own poll) | simulatuvoto's `encuesta_respuestas` table | Phase 2 |
| 12 | Public aggregate view | simulatuvoto's `encuesta_resultados_publicos` SQL view | Phase 2 |
| 13 | Device token / anonymous session | simulatuvoto's `useDeviceToken` | Phase 2 |
| 14 | Article/News | encuestas.com.pe (blog) — only as an example of what NOT to do without structure | Phase 3 (optional) |
| 15 | B2B client (paying candidate/party) | Product 2 in `business-model.md` | Phase 3 |
| 16 | Historical trend (candidate × round) | confirmed gap — no reference shows this (see BL-14 in `backlog.md`) | MVP-adjacent |
| 17 | Shareable result card | simulatuvoto's `Card*`, adapted to poll results (not a simulator) — see BL-15 | Phase 2 |
| 18 | Pollster rating / house effect | 538 methodology — see BL-30 | Phase 4 |

---

## 1. MVP statics (`/data/*.json`, hand-edited, no build)

```json
// partido.json
{ "id": 1, "nombre": "Fuerza Popular", "siglas": "FP", "color": "#FF6B00", "logo": "/logos/fp.png" }
```

```json
// distrito.json (Lima Metropolitana)
{ "id": "san-isidro", "nombre": "San Isidro", "provincia": "lima", "region": "lima" }
```

```json
// candidato.json
{
  "id": 1, "nombre": "...", "partidoId": 1,
  "cargo": "alcalde_lima" | "alcalde_distrital" | "gobernador_regional" | "consejero_regional",
  "distritoId": "san-isidro" | null,
  "foto": "/candidatos/1.jpg", "numero": 3, "activo": true
}
```

```json
// encuestadora.json (source catalog — own + institutional third parties)
{ "id": "iep", "nombre": "Instituto de Estudios Peruanos", "tipo": "institucional", "web": "https://estudiosdeopinion.iep.org.pe" }
// others: "ipsos", "datum", "cpi" (tipo: institucional) — "propia" (tipo: propia, our own, online opt-in)
```

```json
// encuesta.json (round metadata — third-party OR own, same shape)
{
  "id": "2026-06-lima-alcaldia-cpi", "cargo": "alcalde_lima",
  "ambito": "lima_metropolitana" | "distrital" | "regional",
  "distritoId": null, "fechaInicio": "2026-06-01", "fechaFin": "2026-06-15",
  "tamanoMuestra": 500, "margenError": 4.4, "nivelConfianza": 95,
  "modalidad": "presencial" | "telefonica" | "online_probabilistica" | "online_opt_in",
  "metodologia": "free text describing the fieldwork",
  "encuestadoraId": "iep" | "ipsos" | "datum" | "cpi" | "propia",
  "fuentePdf": "https://.../informe.pdf" | null
}
```

Key differentiation: `encuestadoraId: "propia"` + `modalidad: "online_opt_in"` is our interactive poll (visitor votes, same mechanism as `encuestas.com.pe` but with a visible, honest fact sheet about its sampling limits). Everything else is a physical/institutional poll we **aggregate and display with source attribution**, not one we run — same `encuesta.json`/`resultados-lima.json` structure, so both list and compare side by side by `cargo`+`ambito` with no source-specific logic.

```json
// resultados-lima.json (what the page consumes, denormalized)
{
  "encuestaId": "2026-06-lima-alcaldia", "actualizado": "2026-07-10T00:00:00Z",
  "resultados": [
    { "candidatoId": 1, "porcentaje": 24.5, "tendencia": "sube", "cambioPorcentaje": 1.2 }
  ],
  "indecisos": 12.3, "votoBlancoNulo": 4.1
}
```

## 2. Future backend/DB (v2, if the interactive own poll gets activated)

Same pattern as simulatuvoto's `encuesta_respuestas` (enums `sexo_tipo`, `edad_grupo_tipo`, `nse_tipo`, `likely_voter_tipo`, `firmeza_voto_tipo`, `device_tipo` all reusable):

```sql
CREATE TABLE encuesta_respuestas_municipales (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  session_id TEXT NOT NULL UNIQUE,
  distrito_id TEXT,
  sexo sexo_tipo, edad_grupo edad_grupo_tipo, nse nse_tipo,
  likely_voter likely_voter_tipo,
  intencion_alcalde_distrital_candidato_id SMALLINT,
  intencion_alcalde_lima_candidato_id SMALLINT,
  intencion_firmeza firmeza_voto_tipo,
  intencion_voto_blanco BOOLEAN DEFAULT false,
  temas_prioritarios TEXT[],
  device_type device_tipo, ip_hash TEXT,
  created_at TIMESTAMPTZ DEFAULT now()
);
-- + public aggregate view, same pattern as encuesta_resultados_publicos
```

## 3. Relationship between layers

`resultados-lima.json` = manual snapshot (or exported from the aggregate view once a backend exists). MVP doesn't need a backend — hand-written JSON covers the results view (`BL-13`) and the candidate directory (`BL-10`).

With `encuestadoraId` in `encuesta.json`, a single "Lima Mayoralty" page can list N `encuesta.json` entries (IEP, CPI, Datum, Ipsos, own) for the same `cargo`, each with its own fact sheet and chart — that's what competes directly against `encuestas.com.pe` (which doesn't compare sources) and gets close to the G1/538 pattern (multi-institute aggregation).

## Portability notes

- This project's stack is **static (HTML/CSS/JS, no build)** — decision already made (`CLAUDE.local.md`). Entities 1-10 (MVP) are modeled as hand-written static JSON, **not** Supabase tables yet.
- Entities 11-13 (own poll with real collection) do need a backend — when that phase activates, simulatuvoto's schema (`001_encuesta_respuestas.sql`) is a direct starting point: the same stratification enums apply equally to a municipal election, only the geographic scope changes (district instead of national region) along with the office being evaluated.
