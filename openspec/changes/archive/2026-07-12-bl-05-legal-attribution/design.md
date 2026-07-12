## Context

Third content page — file-layout, language, and token conventions are already settled (`BL-02`/`BL-03`/`BL-04`, this design just applies them, doesn't re-derive them). Two genuinely new problems: (1) how to state the JNE-photo-reuse uncertainty honestly, without either false confidence or unusable hedging; (2) the correction process needs a **working contact channel today** — but `BL-06` (authority shell), which will ship the site's proper contact info (email + WhatsApp), hasn't shipped yet and is numbered after this item. A correction page that points to a channel that doesn't exist yet is worse than no page.

## Goals / Non-Goals

**Goals:**
- Give the three legal rules (pollster citation, photo-reuse flag, judicial-record correction) a single anchor page other specs can gate on.
- Pick a correction contact channel that's real and functioning on publish day, not a placeholder email nobody monitors.
- Set up `BL-24`'s dependency on this page as a re-review gate (same pattern as `BL-03`→`BL-25`), not a one-time existence check.

**Non-Goals:**
- A contact form or dedicated support email — that's `BL-06`'s job. This page uses whatever real channel exists today and can be updated with a nicer one later without changing the underlying process.
- Legal sign-off — baseline rules, escalate if a real dispute happens (per `CLAUDE.md` constraint 8: "when in doubt, publish less").

## Decisions

1. **Correction channel: link to the public GitHub repo's Issues, not an email.** The repo is already public with Issues enabled (confirmed via `gh api`, no `docs/devsecops.md` change needed). Using `https://github.com/jackthony/encuestaselectorales-web/issues/new` as the correction-request channel is real, works today, requires no new infrastructure decision, and — as a side benefit — makes corrections publicly timestamped and auditable, which fits a "transparent aggregator" positioning better than a private inbox would. Explicitly noted on the page as the channel "for now," so swapping to a formal contact method (`BL-06`) later is an edit, not a redesign.
2. **JNE photo line: state the uncertainty as a fact, not resolve it.** Simulatuvoto's code assumed JNE photos were reusable but never verified it in writing (per `docs/data-model.md`/`backlog.md` notes). This page states plainly: "asumimos que las fotos oficiales del JNE son de uso público para fines cívicos/informativos, basados en cómo se han usado en proyectos anteriores — esto no ha sido confirmado legalmente." Honest hedging beats false confidence; if a rights-holder objects, the page already shows good faith and a stated (not hidden) assumption.
3. **Pollster citation rule: state the mechanism, not just the principle.** "Citamos la cifra + enlazamos al PDF fuente; nunca reproducimos el informe completo" is concrete enough to be checkable — a future PR that pastes a whole PDF's tables in violates a written, specific rule, not just a vague "be respectful of sources" gesture.
4. **`BL-24` gate wording**: same treatment as `BL-03`'s decision 2 for `BL-25` — `docs/backlog.md` `BL-24`'s entry must say "re-review this page's correction process before shipping," not just "depends on BL-05." Implemented as a task in this change (editing `BL-24`'s existing entry), same as `BL-03` did for `BL-25`.
5. **File path: `/fuentes-correcciones.html`**, anchor `#fuentes-correcciones` — flat root, same convention.

## Risks / Trade-offs

- **[Risk]** GitHub Issues as the correction channel means anyone can see (and potentially pile onto) a correction request, which could be uncomfortable for a candidate disputing a judicial-record claim. **Mitigation**: acceptable for now — transparency is consistent with the site's positioning, and nothing stops a private escalation path from being added later (`BL-06`) without removing the public option.
- **[Risk]** The JNE-photo hedge ("no ha sido confirmado legalmente") could itself read as an admission of risk if scrutinized. **Mitigation**: still better than silence or false certainty — `CLAUDE.md` constraint 8's own rule is "when in doubt, publish less," and this is the "publish the doubt honestly" version of that, not resolved by pretending certainty exists.
