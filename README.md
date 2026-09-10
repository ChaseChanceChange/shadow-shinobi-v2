# Shadow Shinobi

Fresh rebuild of Shadow Shinobi: a persistent browser-based squad management RPG built around tactical squads, deep personalized gear, extreme enhancement risk, and a Living World Memory system.

## Core fantasy

Build a squad. Find powerful weapons and relics. Train operatives. Push equipment beyond safe limits. Risk three or more gear pieces at a time. Win and create extraordinary equipment. Fail and lose the chosen item — but leave fragments behind and potentially create a Lost Relic that can later return to the world through exploration or a global event.

## Core weapon system

Operatives can receive personal core weapons. Weapon rarity controls how much of the weapon's hidden talent tree becomes visible when equipped. Talent trees are data-driven.

## World Memory

Meaningful events are recorded as structured facts. High-value gear failures can become world-level history, creating relic seeds and eventually server-wide events without inventing false history.

## Asset library

`assets/source-library/` is the supplied Shadow Shinobi source asset pool. The current archive contains 7,894 files in 22 folders. The source pool is intentionally kept outside Git tracking because it is raw working material; approved/optimized production assets can be committed under the production asset path.

## Run locally

```bash
docker compose up -d --build
```

Open `http://localhost:8080/`.

The demo database seeds a commander, two operatives, starter gear and missions. The dashboard includes a working enhancement ritual so the risk/reward loop can be tested immediately.
