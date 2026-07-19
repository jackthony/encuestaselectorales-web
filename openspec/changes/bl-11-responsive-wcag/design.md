# BL-11 — Design

## Priority 1 — The permission-denied recovery modal

With GPS mandatory (BL-13), the denial path is where most visitors stop. It is the highest-leverage conversion surface on the site, and the prototype ships it as `alert("Permiso denegado")`.

### Hard browser constraint that shapes the whole design

Once a user denies geolocation, **the site cannot re-prompt**. `getCurrentPosition()` returns `PERMISSION_DENIED` immediately, with no dialog. The permission is only recoverable through the browser's own UI — the padlock/tune icon in the address bar on desktop, or Site settings on mobile.

Therefore the CTA cannot be "retry". It must be *instructions*, followed by a retry that only works after the user has acted outside the page.

### Detect before prompting

Use the Permissions API to read state before triggering the dialog:

```js
navigator.permissions.query({name: 'geolocation'})  // 'granted' | 'prompt' | 'denied'
```

If already `denied` on page load, show the recovery modal directly — never fire a prompt that silently fails and leaves the user staring at a dead button. Feature-detect: Safari's support has historically lagged, so absence of `navigator.permissions` falls through to the normal request path.

### Modal content

Candidate list hides. Modal shows:

1. **Title** — "Necesitamos tu ayuda para proteger esta encuesta."
2. **Why** — "Para evitar que granjas de bots manipulen los resultados de [Distrito], validamos tu presencia física al momento de votar. En Perú las redes móviles comparten una misma IP entre miles de usuarios, así que la IP sola no distingue a una persona real de un bot."
3. **What is stored** — "Guardamos la coordenada del momento del voto, cifrada, junto con tu voto. No te seguimos entre sesiones, no cruzamos tus datos con otras fuentes, y no vendemos ni compartimos tu ubicación." Links to `/politica-privacidad.html`.
4. **Primary CTA** — "Cómo habilitar mi ubicación", expanding a browser-specific mini-guide.
5. **Secondary** — "Ya lo habilité, reintentar", which re-queries the Permissions API.

### Copy constraint (binding)

The modal MUST NOT claim the site does not store location, or that the coordinate is anonymous. BL-13 stores `gps_lat`/`gps_lng` permanently alongside a reversible AES-256-GCM encrypted IP. A precise coordinate plus a timestamp is arguably personal data under Ley 29733.

Point 3 above is worded to describe what the system actually does. Overclaiming here is worse than not shipping the modal: a privacy promise the database contradicts is the single fastest way to lose the trust this product sells. Any copy change to this modal is reviewed against the schema, not just for tone.

### Mini-guide

Detect browser via UA, show one path, offer "otro navegador" to reveal the rest. Chrome/Edge Android, Safari iOS, Chrome/Edge desktop, Firefox. Static images or inline SVG — no video, no external assets.

If detection fails, show all paths rather than none.

### Instrumentation

None built. Denial rate = `1 − (votes ÷ district-page views)`, and BL-21 analytics supplies the denominator. No counter, no endpoint, no table.

## Priority 2 — Carried-forward defects

| Item | Fix |
|---|---|
| Chart.js from jsDelivr unpinned (`perfil_de_candidato.html`) | pin an exact version — an upstream breaking release silently kills the page |
| `alert()` calls in the vote flow | replaced by the modal above and by inline validation messages |
| Missing `aria-label` on icon-only controls | audit all partials |
| Focus not trapped in the modal | keyboard users tab out into the hidden page behind it |

## Priority 3 — WCAG AA contrast

The Canvas palette was adopted wholesale in BL-10 without measurement. Measure every foreground/background pair now in use.

`brand.green` `#15ba75` is the specific suspect: as text on white it measures well under 4.5:1 and fails AA. Expected outcome is a split like the one BL-04 already made for blue — keep `#15ba75` for non-text indicators (bars, dots, borders, which need only 3:1), introduce a darker variant of the same hue for anything that is text.

Do not change `brand.blue` `#102f86` — it is dark enough to pass as text and it is the brand anchor.

Deliverable: a measured table of every pair, with pass/fail, committed alongside the fix.
