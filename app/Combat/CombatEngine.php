<?php
declare(strict_types=1);
namespace ShadowShinobi\Combat;
use InvalidArgumentException;
final class CombatEngine
{
    public const VERSION = '0.1.0';
    public static function start(array $allies, array $enemy): array
    {
        $units=[];
        foreach ($allies as $i=>$raw) { $id='a'.($i+1); $units[$id]=self::unit($id,'ally',$raw); }
        $units['e1']=self::unit('e1','enemy',$enemy);
        $state=['version'=>self::VERSION,'round'=>1,'active_id'=>null,'status'=>'active','units'=>$units,'events'=>[]];
        self::next($state); self::event($state,'battle_start',['message'=>'The ruined shrine exhales ash. The hunt begins.']); return $state;
    }
    public static function act(array $state,string $actorId,string $action): array
    {
        if (($state['status']??'')!=='active' || ($state['active_id']??'')!==$actorId) throw new InvalidArgumentException('Invalid turn.');
        if (($state['units'][$actorId]['side']??'')!=='ally') throw new InvalidArgumentException('Only ally turns accept actions.');
        if (!in_array($action,['quick','shadow','guard','signature'],true)) throw new InvalidArgumentException('Unknown action.');
        $enemyId=self::enemy($state); if (!$enemyId) return $state;
        $actor=&$state['units'][$actorId]; $before=count($state['events']);
        if ($action==='guard') { $actor['guard']=true; $actor['energy']=min($actor['max_energy'],$actor['energy']+10); self::event($state,'guard',['actor'=>$actorId]); }
        else {
            if ($action==='shadow') { if($actor['energy']<25) throw new InvalidArgumentException('Not enough essence.'); $actor['energy']-=25; $base=random_int(38,58)+(int)floor($actor['skill_power']*.38); $label='Shadow Art'; $crit=.22; }
            elseif($action==='signature') { if($actor['energy']<100) throw new InvalidArgumentException('Signature requires 100 essence.'); if($state['units'][$enemyId]['hp']/$state['units'][$enemyId]['max_hp']>.35) throw new InvalidArgumentException('Crimson Sever requires the target below 35%.'); $actor['energy']=0; $base=random_int(115,165)+(int)floor($actor['skill_power']*.72); $label='Crimson Sever'; $crit=.35; }
            else { $base=random_int(24,40)+(int)floor($actor['attack']*.20); $label='Quick Strike'; $crit=.12; }
            self::damage($state,$actorId,$enemyId,$base,$crit,$label,$action==='signature');
            if($action==='shadow' && $state['units'][$enemyId]['hp']>0 && random_int(1,100)<=55){ $state['units'][$enemyId]['bleed']=min(3,(int)($state['units'][$enemyId]['bleed']??0)+1); self::event($state,'status',['target'=>$enemyId,'status'=>'bleed','stacks'=>$state['units'][$enemyId]['bleed']]); }
        }
        if (($state['units'][$enemyId]['hp']??0)<=0){ $state['status']='victory'; self::event($state,'battle_end',['result'=>'victory']); }
        else { self::enemyAttack($state,$enemyId); self::bleed($state); if($state['status']==='active') self::next($state); }
        $state['events_tail']=array_slice($state['events'],$before); return $state;
    }
    private static function unit(string $id,string $side,array $r):array { $hp=max(1,(int)($r['health']??500)); $e=max(100,(int)($r['essence_capacity']??100)); return ['id'=>$id,'side'=>$side,'name'=>(string)($r['name']??'Wraith'),'class'=>(string)($r['class_name']??'Combatant'),'role'=>(string)($r['role_name']??'Combatant'),'hp'=>$hp,'max_hp'=>$hp,'attack'=>max(1,(int)($r['attack']??50)),'defense'=>max(0,(int)($r['defense']??20)),'speed'=>max(1,(int)($r['speed']??20)),'skill_power'=>max(1,(int)($r['skill_power']??25)),'critical_rate'=>(float)($r['critical_rate']??5),'critical_damage'=>(float)($r['critical_damage']??150),'energy'=>$side==='enemy'?$e:$e,'max_energy'=>$e,'guard'=>false,'bleed'=>0]; }
    private static function damage(array &$s,string $a,string $t,int $base,float $crit,string $label,bool $finish=false):void { $u=$s['units'][$a]; $v=&$s['units'][$t]; $mit=max(.55,1-$v['defense']/($v['defense']+180)); $d=max(1,(int)floor($base*$mit)); $isCrit=random_int(1,10000)<=round(max($crit,$u['critical_rate']/100)*10000); if($isCrit)$d=max($d+1,(int)floor($d*$u['critical_damage']/100)); $v['hp']=max(0,$v['hp']-$d); self::event($s,'damage',['actor'=>$a,'target'=>$t,'amount'=>$d,'critical'=>$isCrit,'finisher'=>$finish,'label'=>$label,'remaining_hp'=>$v['hp']]); }
    private static function enemyAttack(array &$s,string $id):void { $targets=array_filter($s['units'],fn($u)=>$u['side']==='ally'&&$u['hp']>0); if(!$targets){$s['status']='defeat';self::event($s,'battle_end',['result'=>'defeat']);return;} uasort($targets,fn($x,$y)=>$x['hp']<=>$y['hp']); $t=(string)array_key_first($targets); $v=&$s['units'][$t]; $e=$s['units'][$id]; $d=max(1,(int)floor((random_int(22,36)+(int)floor($e['attack']*.18))*max(.55,1-$v['defense']/($v['defense']+180)))); if($v['guard']){$d=max(1,(int)floor($d*.42));$v['guard']=false;} $v['hp']=max(0,$v['hp']-$d); self::event($s,'enemy_attack',['actor'=>$id,'target'=>$t,'amount'=>$d,'remaining_hp'=>$v['hp']]); }
    private static function bleed(array &$s):void { foreach($s['units'] as $id=>&$u){$n=(int)($u['bleed']??0);if($n>0&&$u['hp']>0){$d=min($u['hp'],7*$n);$u['hp']-=$d;$u['bleed']=$n-1;self::event($s,'bleed_tick',['target'=>$id,'amount'=>$d,'remaining_hp'=>$u['hp']]);}} }
    private static function next(array &$s):void { $l=array_filter($s['units'],fn($u)=>$u['hp']>0);if(!$l){$s['status']='defeat';return;} uasort($l,fn($a,$b)=>$a['speed']===$b['speed']?strcmp($a['id'],$b['id']):$b['speed']<=>$a['speed']); $s['active_id']=(string)array_key_first($l); $s['round']++; $s['phase']=$s['units'][$s['active_id']]['side']==='ally'?'player':'enemy'; }
    private static function enemy(array $s):?string { foreach($s['units'] as $id=>$u)if($u['side']==='enemy'&&$u['hp']>0)return$id; return null; }
    private static function event(array &$s,string $type,array $data):void{$s['events'][]=['type'=>$type,'at'=>microtime(true),'data'=>$data];}
}
