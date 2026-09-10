<?php
declare(strict_types=1);

namespace ShadowShinobi\World;

use PDO;
use ShadowShinobi\Combat\EncounterCatalog;

final class OverworldService
{
    public const MAP_KEY = 'ashen-frontier';
    public const WIDTH = 96;
    public const HEIGHT = 60;
    private const MOVE_COOLDOWN_MS = 120;

    /** @return array<string,mixed> */
    public static function map(): array
    {
        return [
            'key' => self::MAP_KEY,
            'name' => 'The Ashen Frontier',
            'subtitle' => 'A shattered province where the old shinobi orders died.',
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
            'spawn' => ['x' => 10, 'y' => 45],
            'zones' => [
                ['key'=>'night-cell','name'=>'Night Cell HQ','type'=>'safe','x'=>3,'y'=>38,'w'=>19,'h'=>17,'encounter_rate'=>0.0,'description'=>'The last defensible refuge of the Night Cell.'],
                ['key'=>'ashline','name'=>'Ashline Ruins','type'=>'ruins','x'=>20,'y'=>31,'w'=>25,'h'=>23,'encounter_rate'=>0.055,'description'=>'Crumbling streets, collapsed watchtowers and buried caches.'],
                ['key'=>'red-vale','name'=>'Red Vale','type'=>'wild','x'=>15,'y'=>4,'w'=>31,'h'=>27,'encounter_rate'=>0.09,'description'=>'A dead forest stained red by an old massacre.'],
                ['key'=>'black-rain','name'=>'Black Rain District','type'=>'district','x'=>46,'y'=>30,'w'=>24,'h'=>24,'encounter_rate'=>0.125,'description'=>'A drowned industrial quarter beneath permanent black weather.'],
                ['key'=>'glass-cathedral','name'=>'Glass Cathedral','type'=>'relic','x'=>69,'y'=>34,'w'=>21,'h'=>20,'encounter_rate'=>0.16,'description'=>'A fractured shrine complex built around a fallen relic core.'],
                ['key'=>'voidscar','name'=>'Voidscar','type'=>'void','x'=>48,'y'=>3,'w'=>43,'h'=>27,'encounter_rate'=>0.205,'description'=>'The wound in the world. Reality behaves badly here.'],
            ],
            'landmarks' => [
                ['id'=>'hq','name'=>'Night Cell HQ','kind'=>'hq','x'=>12,'y'=>46,'radius'=>5,'description'=>'Recover, manage the cell, and prepare the next hunt.'],
                ['id'=>'old-gate','name'=>'Ash Gate','kind'=>'ruin','x'=>24,'y'=>41,'radius'=>3,'description'=>'A ruined gate still bearing an erased clan crest.'],
                ['id'=>'red-shrine','name'=>'Shrine of the Red Thread','kind'=>'shrine','x'=>31,'y'=>15,'radius'=>3,'description'=>'A blood-dark shrine where relic echoes collect.'],
                ['id'=>'dead-drop','name'=>'Dead Drop Nine','kind'=>'cache','x'=>42,'y'=>27,'radius'=>2,'description'=>'An abandoned courier cache hidden in a burned-out tower.'],
                ['id'=>'rain-works','name'=>'Black Rain Works','kind'=>'ruin','x'=>58,'y'=>42,'radius'=>4,'description'=>'An industrial ruin haunted by things that remember the living.'],
                ['id'=>'veil-market','name'=>'Veil Market','kind'=>'hub','x'=>66,'y'=>34,'radius'=>3,'description'=>'A black-market camp trading in maps, fragments and names.'],
                ['id'=>'cathedral','name'=>'Glass Cathedral','kind'=>'relic','x'=>79,'y'=>45,'radius'=>6,'description'=>'A broken cathedral surrounding a colossal buried relic.'],
                ['id'=>'void-gate','name'=>'Voidscar Gate','kind'=>'boss','x'=>77,'y'=>16,'radius'=>5,'description'=>'A sealed breach that should not be opened lightly.'],
                ['id'=>'fallen-obelisk','name'=>'Fallen Obelisk','kind'=>'relic','x'=>57,'y'=>11,'radius'=>2,'description'=>'A relic marker inverted by the Voidscar.'],
                ['id'=>'execution-yard','name'=>'Execution Yard','kind'=>'elite','x'=>39,'y'=>35,'radius'=>3,'description'=>'The Bloodbound kept this place pristine. The blood did not fade.'],
            ],
            'roads' => [
                ['x1'=>12,'y1'=>46,'x2'=>24,'y2'=>41],
                ['x1'=>24,'y1'=>41,'x2'=>31,'y2'=>15],
                ['x1'=>24,'y1'=>41,'x2'=>58,'y2'=>42],
                ['x1'=>58,'y1'=>42,'x2'=>79,'y2'=>45],
                ['x1'=>58,'y1'=>42,'x2'=>66,'y2'=>34],
                ['x1'=>66,'y1'=>34,'x2'=>77,'y2'=>16],
                ['x1'=>31,'y1'=>15,'x2'=>57,'y2'=>11],
                ['x1'=>57,'y1'=>11,'x2'=>77,'y2'=>16],
            ],
        ];
    }

    /** @return array<string,mixed> */
    public static function state(PDO $pdo, int $playerId): array
    {
        self::ensureTable($pdo);
        $stmt = $pdo->prepare('SELECT player_id,map_key,position_x,position_y,energy,steps,discovered_json,last_move_at FROM world_player_state WHERE player_id=?');
        $stmt->execute([$playerId]);
        $row = $stmt->fetch();
        if (!$row) {
            $spawn = self::map()['spawn'];
            $insert = $pdo->prepare('INSERT INTO world_player_state (player_id,map_key,position_x,position_y,energy,steps,discovered_json,last_move_at) VALUES (?,?,?,?,?,?,?,NULL)');
            $insert->execute([$playerId,self::MAP_KEY,$spawn['x'],$spawn['y'],100,0,json_encode([], JSON_THROW_ON_ERROR)]);
            $row = ['map_key'=>self::MAP_KEY,'position_x'=>$spawn['x'],'position_y'=>$spawn['y'],'energy'=>100,'steps'=>0,'discovered_json'=>'[]','last_move_at'=>null];
        }
        return self::decorateState($row);
    }

    /** @return array<string,mixed> */
    public static function move(PDO $pdo, int $playerId, int $dx, int $dy): array
    {
        if (abs($dx) + abs($dy) !== 1) throw new \InvalidArgumentException('Movement must be exactly one tile.');
        self::ensureTable($pdo);
        $current = self::state($pdo, $playerId);
        $x = (int)$current['position_x'] + $dx;
        $y = (int)$current['position_y'] + $dy;
        if (!self::walkable($x, $y)) return self::decorateState($current, 'The way is blocked.');

        $zone = self::zoneAt($x, $y);
        $steps = (int)$current['steps'] + 1;
        $energy = max(0, (int)$current['energy'] - ($steps % 18 === 0 ? 1 : 0));
        $discovered = json_decode((string)$current['discovered_json'], true);
        if (!is_array($discovered)) $discovered = [];
        $newDiscovery = null;
        foreach (self::map()['landmarks'] as $landmark) {
            if (hypot($x - $landmark['x'], $y - $landmark['y']) <= (float)$landmark['radius'] && !in_array($landmark['id'], $discovered, true)) {
                $discovered[] = $landmark['id'];
                $newDiscovery = $landmark;
            }
        }

        $stmt = $pdo->prepare('UPDATE world_player_state SET position_x=?, position_y=?, energy=?, steps=?, discovered_json=?, last_move_at=NOW(6) WHERE player_id=?');
        $stmt->execute([$x,$y,$energy,$steps,json_encode(array_values($discovered), JSON_THROW_ON_ERROR),$playerId]);

        $result = self::state($pdo, $playerId);
        $result['zone_changed'] = ($zone['key'] ?? null) !== ($current['zone']['key'] ?? null);
        $result['message'] = self::movementMessage($result, $current);
        if ($newDiscovery !== null) {
            $result['discovery'] = ['id'=>$newDiscovery['id'],'name'=>$newDiscovery['name'],'kind'=>$newDiscovery['kind'],'description'=>$newDiscovery['description']];
            $result['message'] = 'DISCOVERY: ' . $newDiscovery['name'] . ' added to your world memory.';
        }
        $result['relic_discovery'] = self::discoverLostRelic($pdo, $playerId, $x, $y);
        if ($result['relic_discovery'] !== null) $result['message'] = 'RELIC DISCOVERED: ' . $result['relic_discovery']['name'] . '.';
        $result['encounter'] = self::rollEncounter($zone, $x, $y, $steps);
        return $result;
    }

    public static function walkable(int $x, int $y): bool
    {
        if ($x < 0 || $y < 0 || $x >= self::WIDTH || $y >= self::HEIGHT) return false;
        $blocked = [
            [4,38,9,4],[18,37,2,9],[27,48,4,6],[34,39,3,7],[41,34,5,3],[49,48,4,6],[53,36,4,5],[62,45,3,6],[72,38,4,4],[83,47,5,5],
            [20,8,5,5],[28,21,6,4],[36,7,4,6],[43,17,5,4],[51,5,6,4],[60,22,7,5],[68,7,5,5],[84,9,4,6],[75,27,8,3],
        ];
        foreach ($blocked as [$bx,$by,$bw,$bh]) if ($x >= $bx && $x < $bx+$bw && $y >= $by && $y < $by+$bh) return false;
        if ($x >= 55 && $x <= 57 && $y >= 14 && $y <= 26 && $y % 2 === 0) return false;
        if ($x >= 71 && $x <= 73 && $y >= 4 && $y <= 28 && $y % 3 !== 0) return false;
        return true;
    }

    /** @return array<string,mixed> */
    public static function zoneAt(int $x, int $y): array
    {
        foreach (self::map()['zones'] as $zone) if ($x >= $zone['x'] && $x < $zone['x']+$zone['w'] && $y >= $zone['y'] && $y < $zone['y']+$zone['h']) return $zone;
        return ['key'=>'frontier','name'=>'Outer Frontier','type'=>'frontier','encounter_rate'=>0.07,'description'=>'Unmapped ground at the edge of the known province.'];
    }

    /** @return array<string,mixed> */
    private static function decorateState(array $row, ?string $message = null): array
    {
        $state = [
            'map_key'=>(string)$row['map_key'],'position_x'=>(int)$row['position_x'],'position_y'=>(int)$row['position_y'],'energy'=>(int)$row['energy'],'steps'=>(int)$row['steps'],
            'discovered'=>json_decode((string)$row['discovered_json'], true) ?: [],'last_move_at'=>$row['last_move_at'],
            'zone'=>self::zoneAt((int)$row['position_x'], (int)$row['position_y'])
        ];
        if ($message !== null) $state['message'] = $message;
        return $state;
    }

    /** @return array<string,mixed>|null */
    private static function discoverLostRelic(PDO $pdo, int $playerId, int $x, int $y): ?array
    {
        try {
            $rows = $pdo->query("SELECT id,name,payload_json FROM lost_relics WHERE discovered=0 AND payload_json IS NOT NULL ORDER BY id ASC LIMIT 100")->fetchAll();
        } catch (\Throwable) { return null; }
        foreach ($rows as $row) {
            $payload = json_decode((string)$row['payload_json'], true);
            if (!is_array($payload) || ($payload['map_key'] ?? null) !== self::MAP_KEY) continue;
            $rx = (int)($payload['position_x'] ?? -999); $ry = (int)($payload['position_y'] ?? -999);
            if (hypot($x-$rx,$y-$ry) > 1.5) continue;
            $claim = $pdo->prepare('UPDATE lost_relics SET discovered=1,discovered_by_player_id=?,discovered_at=NOW() WHERE id=? AND discovered=0');
            $claim->execute([$playerId,(int)$row['id']]);
            if ($claim->rowCount() !== 1) continue;
            return ['id'=>(int)$row['id'],'name'=>(string)$row['name'],'fragment_index'=>(int)($payload['fragment_index'] ?? 0),'fragment_total'=>(int)($payload['fragment_total'] ?? 1)];
        }
        return null;
    }

    /** @return array<string,mixed>|null */
    private static function rollEncounter(array $zone, int $x, int $y, int $steps): ?array
    {
        $rate = (float)($zone['encounter_rate'] ?? 0.06);
        if ($rate <= 0 || (($steps % 2) === 0 && $rate < 0.1)) return null;
        $seed = abs(crc32(self::MAP_KEY . ':' . $x . ':' . $y . ':' . $steps));
        if ((($seed % 100000) / 100000) >= $rate) return null;
        $catalog = EncounterCatalog::all(); $keys = array_keys($catalog);
        $threat = match ((string)$zone['type']) {'safe'=>0,'ruins'=>4,'wild'=>5,'district'=>6,'relic'=>8,'void'=>10,default=>4};
        $eligible = array_values(array_filter($keys, static fn(string $key): bool => (int)$catalog[$key]['threat'] <= max(4,$threat+2)));
        if (!$eligible) $eligible=[$keys[0]];
        $picked=$eligible[$seed % count($eligible)];
        return ['key'=>$picked,'name'=>$catalog[$picked]['name'],'threat'=>(int)$catalog[$picked]['threat'],'zone'=>(string)$zone['name'],'reason'=>'A hostile presence has found your trail.'];
    }

    private static function movementMessage(array $state, array $old): string
    {
        if (($state['zone']['key'] ?? '') !== ($old['zone']['key'] ?? '')) return 'Entering ' . (string)$state['zone']['name'] . '.';
        $messages=['Ash shifts beneath your feet.','The wind carries something that sounds like steel.','A distant bell rings once, then stops.','Your cell moves deeper into the ruins.','Nothing moves. That is what worries you.'];
        return $messages[((int)$state['steps']) % count($messages)];
    }

    private static function ensureTable(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS world_player_state (player_id BIGINT UNSIGNED PRIMARY KEY,map_key VARCHAR(64) NOT NULL DEFAULT 'ashen-frontier',position_x INT NOT NULL DEFAULT 10,position_y INT NOT NULL DEFAULT 45,energy INT NOT NULL DEFAULT 100,steps BIGINT UNSIGNED NOT NULL DEFAULT 0,discovered_json JSON NOT NULL,last_move_at DATETIME(6) NULL,updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,CONSTRAINT fk_world_player_state_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
