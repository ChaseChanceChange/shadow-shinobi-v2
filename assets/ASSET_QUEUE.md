# Shadow Shinobi Asset Queue

This queue tracks assets from the supplied `official-shadow-shinobi-assets-madeby-ChaseCraft.zip` library before they are promoted into runtime production.

## Priority A — combat roster

| Candidate | Source path inside supplied archive | Intended use | Status |
|---|---|---|---|
| The Veiled Blade | `characters/1shadow-shinobi-fullbod2.png` | Starter striker / assassin visual | Candidate reviewed |
| The Horned Bulwark | `characters/1shadow-shinobi-fullbod2.png` | Tank / guardian visual | Candidate reviewed |
| The Unseen Oracle | `characters/1shadow-shinobi-fullbod2.png` | Support / occult caster visual | Candidate reviewed |
| The Broken Alchemist | `characters/1shadow-shinobi-fullbod2.png` | Debuff / bleed specialist | Candidate reviewed |
| Voidweaver Cultist | `characters/1shadow-shinobi-fullbod2.png` | Enemy occult caster | Candidate reviewed |
| The Shardsoul Golem | `characters/1shadow-shinobi-fullbod2.png` | Heavy construct enemy | Candidate reviewed |
| Void Herald | `characters/1shadow-shinobi-fullbod2.png` | Elite / boss caster | Candidate reviewed |

## Import rules

1. Do not place the original source archive in the production web root.
2. Promote only assets that are actually used by a page or system.
3. Prefer cropped/cleaned individual character renders over giant contact sheets when possible.
4. Every promoted asset should have a source entry with the original archive path and intended game use.
5. Keep original art editable outside the runtime tree so new versions can replace production files without changing gameplay data.

## Current production asset family

The repo already contains a production weapon asset under `assets/production/weapons/`. New character and effect assets should follow the same `assets/production/<family>/` structure.
