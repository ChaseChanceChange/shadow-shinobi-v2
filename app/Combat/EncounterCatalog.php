<?php
declare(strict_types=1);

namespace ShadowShinobi\Combat;

final class EncounterCatalog
{
    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        return [
            'red-vale-wraith' => [
                'name' => 'Wraith of the Red Vale',
                'class_name' => 'Apex Hunter',
                'role_name' => 'Bleed Predator',
                'health' => 760,
                'attack' => 118,
                'defense' => 92,
                'speed' => 44,
                'skill_power' => 80,
                'essence_capacity' => 100,
                'threat' => 4,
            ],
            'bloodbound-executioner' => [
                'name' => 'Bloodbound Executioner',
                'class_name' => 'Ruin Enforcer',
                'role_name' => 'Heavy Striker',
                'health' => 980,
                'attack' => 142,
                'defense' => 108,
                'speed' => 36,
                'skill_power' => 72,
                'essence_capacity' => 100,
                'threat' => 6,
            ],
            'shardsoul-golem' => [
                'name' => 'Shardsoul Golem',
                'class_name' => 'Relic Construct',
                'role_name' => 'Bulwark',
                'health' => 1320,
                'attack' => 154,
                'defense' => 155,
                'speed' => 24,
                'skill_power' => 64,
                'essence_capacity' => 100,
                'threat' => 8,
            ],
            'void-herald' => [
                'name' => 'Void Herald',
                'class_name' => 'Occult Elite',
                'role_name' => 'Arcane Executioner',
                'health' => 1680,
                'attack' => 176,
                'defense' => 126,
                'speed' => 52,
                'skill_power' => 116,
                'essence_capacity' => 100,
                'threat' => 10,
            ],
        ];
    }

    /** @return array<string,mixed> */
    public static function get(?string $key = null): array
    {
        $all = self::all();
        if ($key !== null && isset($all[$key])) {
            return $all[$key] + ['key' => $key];
        }

        $keys = array_keys($all);
        $selected = $keys[random_int(0, count($keys) - 1)];
        return $all[$selected] + ['key' => $selected];
    }
}
