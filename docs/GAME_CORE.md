# Shadow Shinobi — Game Core Architecture

Shadow Shinobi is built as a persistent systems game, not a collection of presentation screens. Presentation surfaces may evolve independently, while authoritative systems own state and outcomes.

## Core loop

**Command → Prepare → Explore → Encounter → Combat → Reward → World Memory → Improve → Repeat**

## Authoritative systems

### Player state
Owns commander identity, currencies and persistent progression.

### Operative system
Owns roster identity, levels and combat attributes.

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

## Design test

A new feature is considered a core system when its important state survives a page refresh and can be represented in persistent data. A feature is considered presentation when removing its UI does not change the underlying game state.

## Vertical slice standard

Before declaring a major feature complete, it should have:

1. Persistent state.
2. Authoritative validation.
3. Player-facing presentation.
4. A meaningful reward, consequence or choice.
5. A path into the existing game loop.
6. A documented extension point for future content.

The Ashen Frontier is the first vertical slice that exercises the world, persistence, discovery and combat boundaries together.
