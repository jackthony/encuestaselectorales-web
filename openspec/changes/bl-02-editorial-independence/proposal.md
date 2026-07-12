## Why

`encuestaselectorales-web` is positioned as a neutral poll aggregator (`CLAUDE.md` Goal). `docs/business-model.md` Product 2 plans to sell candidates featured profiles and verified badges — real B2B revenue. Without a published, binding rule that separates what's for sale (visibility) from what's never for sale (poll numbers, rankings, aggregation logic), the site is indistinguishable from a pay-to-win operation the moment it takes its first candidate payment. `CLAUDE.md` constraint 3 already states this is non-negotiable; this change makes it a public, linkable policy so the rule is enforceable and verifiable by outsiders, not just an internal intention. It has to exist before `BL-29` (B2B launch) and be ready to link from `BL-05` (methodology page).

## What Changes

- Add a public "Editorial Independence" policy page stating exactly what's for sale (visibility, verified badge, alerts) and what's never for sale (poll results, rankings, aggregation logic, methodology).
- Add a short, plain-language explanation of *why* this firewall exists (so it reads as a principled commitment, not legal boilerplate).
- Establish the page as a required link target for `BL-05` (methodology page) and `BL-29` (B2B pricing page), so both future changes have a fixed anchor to link to.

## Capabilities

### New Capabilities
- `editorial-independence-policy`: a published policy page (and its content rules) establishing the firewall between paid B2B features and editorial/aggregation output.

### Modified Capabilities
(none — first capability in this repo)

## Impact

- New static page (path TBD in design.md) plus a nav/footer link.
- No data model, backend, or build-tooling impact — pure content, per `docs/engineering-standards.md` (content item, checklist not TDD).
- Creates a dependency: `BL-05` and `BL-29` specs must link to this page once it ships.
