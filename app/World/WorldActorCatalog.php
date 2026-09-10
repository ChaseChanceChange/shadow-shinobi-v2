<?php
declare(strict_types=1);

namespace ShadowShinobi\World;

final class WorldActorCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        return [
            ['id'=>'kaori','name'=>'Kaori Venn','kind'=>'quartermaster','x'=>16,'y'=>47,'radius'=>2,'region'=>'night-cell','text'=>'"Bring me fragments, not excuses. I can turn either into something useful."'],
            ['id'=>'mourn','name'=>'Mourn','kind'=>'broker','x'=>65,'y'=>35,'radius'=>2,'region'=>'black-rain','text'=>'A masked broker dealing in maps, names and things that should have stayed buried.'],
            ['id'=>'red-keeper','name'=>'The Red Keeper','kind'=>'lorekeeper','x'=>31,'y'=>14,'radius'=>2,'region'=>'red-vale','text'=>'The shrine attendant records every traveller who asks about the First Blade.'],
            ['id'=>'glass-choir','name'=>'Glass Choir','kind'=>'relic','x'=>80,'y'=>45,'radius'=>3,'region'=>'glass-cathedral','text'=>'Broken voices answer from beneath the cathedral floor.'],
            ['id'=>'void-listener','name'=>'The Listener','kind'=>'boss','x'=>78,'y'=>15,'radius'=>3,'region'=>'voidscar','text'=>'Something on the other side of the wound knows your name.'],
        ];
    }

    /** @return array<string,mixed>|null */
    public static function nearby(int $x, int $y): ?array
    {
        $best = null;
        $bestDistance = PHP_INT_MAX;
        foreach (self::all() as $actor) {
            $distance = hypot($x - $actor['x'], $y - $actor['y']);
            if ($distance <= $actor['radius'] && $distance < $bestDistance) {
                $best = $actor;
                $bestDistance = $distance;
            }
        }
        return $best;
    }
}
