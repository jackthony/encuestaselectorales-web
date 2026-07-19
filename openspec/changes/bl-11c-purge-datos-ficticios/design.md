# BL-11c — Design

## Why deletion, not a "test" flag

The simplest fix that actually satisfies the owner's constraint ("nada ficticio en producción, punto") is deleting the three JSON records and their one PHP accessor function — not adding a flag to suppress them, because a suppress-flag is one config error away from the exact leak this item exists to close. There is no legitimate reason for this specific fabricated triad to exist in the production JSON files at all; it was scaffolding for a schema check, and the schema it validated is already proven (three other pollster entries — `iep`, `ipsos`, `datum` — carry real, structurally identical records). Keeping "ejemplo" around as a template for future test fixtures is not this item's job; if that's wanted later, it belongs in a fixtures directory outside `data/`, not inside the files the live pages read.

## Each page's fallback is its existing empty state, not a new one

`sondeos.php`, `encuesta.php`, and `candidato.php` already have (or, per `bl-11`/`bl-11b`, are gaining) real "no data" rendering paths for districts/candidates without real polls — this item doesn't invent new empty-state UI, it just stops these three pages from having fabricated data to render instead of falling through to the empty path they already have or are getting. Where a page's empty-state path doesn't exist yet independent of this item, that gap belongs to whichever item owns that page's broader rebuild, not to this purge.

## Sequencing with `bl-11b`

Both items touch `index.php`-adjacent territory but not the same files: this item never edits `index.php` itself. Order-independent — whichever lands first, the other doesn't need to rebase against it, since neither reintroduces what the other removes.
