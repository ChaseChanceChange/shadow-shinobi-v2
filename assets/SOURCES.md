# External Asset Shortlist

These are candidate sources selected for Shadow Shinobi because their published pages currently state permissive commercial-use terms.

## Priority sources

### Kenney UI Pack
https://kenney.nl/assets/ui-pack

- 430 files listed on the asset page.
- CC0.
- Good for structural UI pieces, buttons, panels and interface states.
- We will recolor/composite rather than letting the default style define the game's identity.

### Kenney UI Pack RPG Expansion
https://kenney.nl/assets/ui-pack-rpg-expansion

- 85 files listed.
- CC0.
- Useful for inventory, item and RPG-facing interface elements.

### Kenney Graveyard Kit
https://kenney.nl/assets/graveyard-kit

- 90 files listed.
- CC0.
- Candidate environment source for ruined/cursed locations and background props.

### OpenGameArt — Gore Blood Gibs Meat Chunks
https://opengameart.org/content/gore-blood-gibs-meat-chunks

- CC0.
- Small focused effect pack for blood, meat, bone and gib effects.
- Use sparingly in finishers so the combat remains readable.

### OpenGameArt — Bloodspatter and Gibs
https://opengameart.org/content/bloodspatter-and-gibs

- CC0.
- Useful 2D impact decals and combat punctuation.

### OpenGameArt — Animations: Blood, Hit and Both :D
https://opengameart.org/content/animations-blood-hit-and-both-d

- CC0.
- Frame-by-frame hit/blood animation source.
- Good candidate for compact browser combat effects.

### OpenGameArt — Ninja Character
https://opengameart.org/content/ninja-character-0

- Published as CC0.
- Contains idle, run, jump, attack, death and roll animation material.
- Treat as supplemental animation reference/placeholder rather than the final Shadow Shinobi identity.

### Quaternius — Free Game Assets
https://quaternius.com/

- Current site provides multiple character, monster, weapon, dungeon and environment packs.
- Quaternius' current license page states free commercial use with no attribution required under the QAL/CC0 terms applicable to the listed assets.
- Priority candidates: Modular Dungeons, Modular Weapons, Ultimate RPG, Animated Monster, Ultimate Animated Character.

## Integration rule

External packs live under clearly named source directories and are never mixed into the authored Shadow Shinobi identity without a manifest entry.

Every imported batch should record:

- source URL
- asset pack name
- license
- local path
- date acquired
- intended game use
- whether modified

The production build should contain only the assets we actually use. Raw source archives stay out of the game runtime.
