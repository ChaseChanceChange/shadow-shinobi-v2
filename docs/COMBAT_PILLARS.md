# Shadow Shinobi Combat Pillars

## The target feeling

Combat should feel closer to a compact mobile/PC squad battler than a database report. The player should read the battlefield immediately, anticipate the next turn, choose an action, and receive a strong visual payoff.

The browser implementation remains lightweight: HTML, CSS and JavaScript first, with server-authoritative PHP for outcomes.

## Encounter structure

1. Pre-battle briefing: enemy silhouettes, threat rating, rewards and known modifiers.
2. Turn order strip: every operative and enemy shows an initiative position.
3. Battlefield stage: large portrait/sprite presentation, health bars and status icons.
4. Action tray: basic attack, skill, guard/support and signature action.
5. Impact layer: slash arcs, screen shake, hit stop, particles, floating damage and status callouts.
6. Finisher state: a short high-impact sequence when the conditions are met.
7. Result board: wounds, XP, loot, fragments, world consequences and history entry.

## Browser-friendly spectacle

Use short CSS animations and DOM layers rather than expensive canvas simulations for normal attacks.

Effects to build:

- 80–140 ms hit-stop on heavy attacks.
- 2–4 px camera shake.
- brief white impact flash.
- slash trail / directional sweep.
- floating damage numbers with critical formatting.
- bleed/burn/stun badges.
- defeated enemy collapse / fade.
- low-health vignette.
- turn-transition pulse.

Gore is used as punctuation, not constant noise. High-damage finishers can trigger a small blood spray, splatter or gib effect while normal hits use restrained blood sparks.

## Tactical depth

Combat must not become an auto-battle button with no meaningful decisions.

Examples of decisions:

- Focus a vulnerable target before its next turn.
- Break an enemy guard instead of maximizing immediate damage.
- Spend a signature ability now or preserve it for the boss phase.
- Protect a bleeding operative.
- Exploit class/role synergy.
- Accept a dangerous damage window for a faster clear.

## MMO layer

The single-player-looking encounter is backed by persistent multiplayer systems later:

- public boss windows
- faction objectives
- world events
- cooperative hunts
- asynchronous rival cells
- leaderboards that measure efficiency and mastery, not paid power
- guild/cell progression

## Architecture

The client may animate an attack immediately for responsiveness, but the server decides the authoritative result. Each combat action produces a compact event record that can be replayed by the browser renderer.

That lets us make combat increasingly cinematic without letting visual effects become the source of truth.
