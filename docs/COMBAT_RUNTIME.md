# Shadow Shinobi Combat Runtime

The combat prototype now has a server-side resolver under `app/Combat/CombatEngine.php` and a session-backed JSON endpoint at `public/api/combat.php`.

## Runtime rules

- The PHP engine decides initiative, damage, criticals, guarding, essence costs, bleed ticks, defeat and victory.
- The browser receives an event stream and turns it into hit flashes, floating damage, shake, bleed callouts and finisher slashes.
- `Crimson Sever` is gated behind 100 essence and a target below 35% HP.
- `Shadow Art` costs 25 essence and has a chance to apply bleed.
- `Guard` reduces the next incoming hit and restores a small amount of essence.
- The combat state lives in the server session during the prototype phase; persistent PvE/PvP combat records come later.

## Test page

Open `/combat.php` from the local PHP container. `/combat-lab.php` remains available as the earlier visual sandbox.

## Next integration layer

The next combat pass should bind real equipment and talent modifiers into the resolver, record completed encounters in `mission_runs` or a dedicated battle table, and replace the placeholder sigils with selected production assets from the Shadow Shinobi asset library.
