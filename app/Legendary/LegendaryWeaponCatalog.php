<?php
declare(strict_types=1);

namespace ShadowShinobi\Legendary;

final class LegendaryWeaponCatalog
{
    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        return [
            'kageboshi' => [
                'key' => 'kageboshi',
                'name' => 'Kageboshi, the Shadow-Splitter',
                'type' => 'Odachi',
                'element' => 'Void / Shadow',
                'rarity' => 'Legendary',
                'base_stat_name' => 'Attack',
                'base_stat_value' => 260,
                'substats' => [
                    ['name'=>'Critical Rate','value'=>10],
                    ['name'=>'Critical Damage','value'=>18],
                    ['name'=>'Speed','value'=>5],
                ],
                'talent_tree_key' => 'kageboshi',
                'shatter_fragments' => 5,
                'fragment_ttl_days' => 7,
                'lore' => 'The first Kageboshi was forged by a nameless smith who tried to cut a shadow in half. The blade succeeded, but the shadow it cut was his own. It whispers memories rather than words.',
                'nodes' => [
                    ['key'=>'severed_reflection','tier'=>1,'name'=>'Severed Reflection','description'=>'Critical hits deal bonus weapon damage against enemies targeting the wielder.','effect'=>['critical_bonus_percent'=>40]],
                    ['key'=>'memory_theft','tier'=>2,'name'=>'Memory Theft','description'=>'Kills store up to five memory fragments. Each grants bonus damage against that enemy type.','effect'=>['max_memory'=>5,'damage_per_memory_percent'=>3]],
                    ['key'=>'shadow_cut','tier'=>3,'name'=>'The Shadow Cut','description'=>'At three or more memories, the next attack cleaves and applies a missing-health bleed.','effect'=>['required_memory'=>3,'arc_degrees'=>180]],
                    ['key'=>'kageboshi_echo','tier'=>4,'name'=>"Kageboshi's Echo",'description'=>'On shatter, five world fragments are created and the weapon memory survives.','effect'=>['fragment_count'=>5,'ttl_days'=>7]],
                ],
            ],
        ];
    }

    /** @return array<string,mixed> */
    public static function get(string $key): array
    {
        $all = self::all();
        if (!isset($all[$key])) throw new \InvalidArgumentException('Unknown legendary weapon.');
        return $all[$key];
    }
}
