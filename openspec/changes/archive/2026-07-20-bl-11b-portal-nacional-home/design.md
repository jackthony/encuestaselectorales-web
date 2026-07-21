# BL-11b — Design

## Why a separate item from BL-11

`bl-11-responsive-wcag` already owns the district-page rebuild, the GPS modal, and the WCAG audit — three workstreams sharing one theme (fixing carried-forward BL-10 defects). The home rebuild is a fourth, unrelated diff surface (`index.php`, not `distrito.php`) driven by a different Canvas file, arriving from a separate business decision (going national). Folding it into BL-11 would mix "fix what BL-10 shipped" with "rebuild a page for a scope BL-10 never had" under one change — keeping them separate keeps each item's structural diff check honest (BL-10's `check-refactor.php` pattern compares one page against one prototype; two prototypes under one item invites conflating them).

## Empty states are two different flavors, not one

The hub's two columns fail differently when empty, and the prototype already reflects this — do not collapse them into one shared "no data" component:

- **Encuestas Web Activas** (own rounds): the empty state is an *invitation* — "¿Quieres medir a tu distrito?" pointing at the search. This column will legitimately stay empty for a while (no round opens until `bl-13b`/BL-14 ship), so its empty state must not read as broken.
- **Estudios de Campo** (third-party): the empty state is *absence of evidence*, not absence of a feature — there is nothing to invite the user to do about a pollster not having published yet. A blank column with a one-line "aún no hay estudios publicados para tu zona" note is enough; do not manufacture a CTA here.

## Search is client-side and scope-agnostic on purpose

The search box filters `data/distrito.json` in `assets/js/app.js`, the same client-side pattern the rest of the pre-BL-16 site uses (no `/api/`, no DB). It is written against the JSON's shape (`id`, `nombre`, `provincia`, `region`), not against a hardcoded count of 43 — so the day `distrito.json` grows to cover all of Peru, this component needs no code change. That data-growth work itself has no owner yet (see proposal.md's out-of-scope section) and is not this item's job to schedule.
