# Shadow Shinobi — Game Core Architecture

Shadow Shinobi is built as a persistent systems game, not a collection of presentation screens. Presentation surfaces may evolve independently, while authoritative systems own state and outcomes.

## Current core loop

**Command → Prepare → Explore → Encounter → Combat → Reward → World Memory → Improve → Repeat**

## Authority boundary

The browser is an input and presentation client. It must not be trusted to award currency, determine combat outcomes, bypass movement rules, or mutate progression directly.

Authoritative flow:

**Input → GameEngine command → subsystem validation → state mutation → event/result → presentation**

## Authoritative systems

### Player state
Owns commander identity, currencies and persistent progression.

### Operative system
Owns roster identity, levels and combat attributes.

### Squad system
Owns squad membership and battle order.

### Equipment system
Owns item identity, rarity, enhancement, awakening, visual state and talent-tree references.

### Combat engine
Owns turn order, actions, damage, status effects, victory/defeat and replayable encounter state. The browser presents the result; it does not decide it.

### Overworld
Owns map position, walkability, regions, landmarks, exploration steps, energy, discoveries and location-driven encounters.

### Living World / World Memory
Owns persistent facts produced by player actions. Important failures are allowed to become future content.

### Lost Relics
Owns items that leave normal inventory through failure or world events and can become discoverable world content.

### Legendary weapons
Use persistent history, branching talents, visual evolution and world-facing failure states. Legendary items are designed as mechanical sidegrades rather than mandatory stat upgrades.

## Presentation layer

The browser UI is responsible for rendering state: maps, sprites, combat effects, menus, inventories, notifications and lore. Presentation can be replaced without changing authoritative rules.

## Vertical slice standard

Before declaring a major feature complete, it should have:

1. Persistent state.
2. Authoritative validation.
3. Player-facing presentation.
4. A meaningful reward, consequence or choice.
5. A path into the existing game loop.
6. A documented extension point for future content.

The Ashen Frontier is the first vertical slice that exercises the world, persistence, discovery and combat boundaries together.

## Engine baseline

Current baseline: `0.1.0-ashen-frontier`.

The `GameEngine` service provides the shared command boundary. New gameplay systems should expose narrow authoritative operations through that boundary instead of embedding business rules directly in page scripts.
