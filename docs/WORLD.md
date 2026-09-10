# Shadow Shinobi v2 — The Ashen Frontier

The overworld is a persistent game system, not a decorative background. It is the connective tissue between the command deck, exploration, encounters, combat, discoveries, future world events and future multiplayer presence.

## World slice in this checkpoint

**Map:** `ashen-frontier`

**Footprint:** 96 × 60 logical tiles.

**Starting region:** Night Cell HQ in the south-west.

**Primary regions:**

- Night Cell HQ — safe refuge and recovery.
- Ashline Ruins — low-threat exploration and buried caches.
- Red Vale — hostile wilderness and the first serious hunting ground.
- Black Rain District — industrial ruins with a higher encounter rate.
- Glass Cathedral — relic territory with elite threats.
- Voidscar — the northern wound in reality and the most dangerous region.
- Outer Frontier — connective territory outside named region boundaries.

## Landmarks

The first world pass establishes ten discoverable points of interest: Night Cell HQ, Ash Gate, Shrine of the Red Thread, Dead Drop Nine, Black Rain Works, Veil Market, Glass Cathedral, Voidscar Gate, Fallen Obelisk and Execution Yard.

Landmarks are intentionally typed. This gives the game a stable vocabulary for future interactions, vendors, relic discoveries, quest hooks, bosses and world events without tying presentation to a single asset pack.

## Traversal model

Movement is tile based and authoritative on the server. The browser renders the map and sends one-tile movement intentions. The server validates bounds and collision, persists the position, tracks exploration steps and records landmark discoveries.

The current renderer is procedural pixel-styled art so that the world is immediately playable without blocking development on the asset archive. Production art can be layered over the same tile/landmark contract later without changing the persistence or combat systems.

## Encounter bridge

Moving through a named region can produce a tactical encounter. The encounter uses the existing server-authoritative combat engine and the existing encounter catalogue. The combat page can therefore become a destination reached from the world rather than a disconnected demo screen.

Current encounter catalogue keys are:

- `red-vale-wraith`
- `bloodbound-executioner`
- `shardsoul-golem`
- `void-herald`

Threat is biased by region, with the Voidscar able to surface the strongest existing encounter.

## Persistence

`world_player_state` stores:

- map key
- x/y position
- exploration energy
- movement step count
- discovered landmark IDs
- last movement timestamp

The runtime creates the table defensively, while `database/003_overworld.sql` records the intended schema for clean installations.

## Expansion contract

The next world passes should add these systems without replacing the map contract:

1. authored tile sprites and environment sets from the project's approved asset pool;
2. animated player/operative sprites with directional frames;
3. interiors and map transitions;
4. NPC schedules and interaction state;
5. resource nodes, relic sites and hidden caches;
6. public events that alter region state;
7. other cells/players rendered from server state;
8. co-op hunts and world bosses;
9. fast travel unlocked through discovered landmarks;
10. weather, time-of-day and region-specific audiovisual layers.

The design target is a world that is readable at a glance, dangerous to traverse, and mechanically relevant to everything the player does outside combat.
