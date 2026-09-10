-- Shadow Shinobi content expansion. Runs after init.sql on a fresh database.
INSERT INTO missions (mission_key,name,description,difficulty,recommended_level,reward_coins,loot_json) VALUES
('silent-dead-drop','Silent Dead Drop','Recover a sealed intelligence packet before the rival cell reaches the exchange.', 'Normal',1,220,'[{"type":"material","name":"Cipher Thread","quantity":2}]'),
('black-rain','Black Rain','Cross a storm-battered district and extract an operative who stopped answering.', 'Hard',3,420,'[{"type":"material","name":"Black Alloy","quantity":2},{"type":"fragment","quantity":2}]'),
('glass-cathedral','Glass Cathedral','Enter a ruined sanctuary where the last security signal is still active.', 'Hard',4,560,'[{"type":"material","name":"Stormglass","quantity":2}]'),
('red-veil','Red Veil Hunt','Track a masked hunter who has been dismantling small cells one by one.', 'Brutal',7,1050,'[{"type":"gear","rarity":"Epic","quantity":1}]'),
('king-in-ash','The King in Ash','A name erased from every archive has resurfaced. Find out why.', 'Nightmare',12,2400,'[{"type":"gear","rarity":"Legendary","quantity":1}]')
ON DUPLICATE KEY UPDATE description=VALUES(description),difficulty=VALUES(difficulty),recommended_level=VALUES(recommended_level),reward_coins=VALUES(reward_coins),loot_json=VALUES(loot_json);

INSERT INTO operatives (player_id,name,class_name,role_name,attack,defense,health,speed,skill_power)
SELECT p.id,'Sable Kest','Controller','Control',94,88,515,70,105 FROM players p
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM operatives o WHERE o.player_id=p.id AND o.name='Sable Kest');
INSERT INTO operatives (player_id,name,class_name,role_name,attack,defense,health,speed,skill_power)
SELECT p.id,'Rook Vale','Bulwark','Tank',82,132,690,42,64 FROM players p
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM operatives o WHERE o.player_id=p.id AND o.name='Rook Vale');
INSERT INTO operatives (player_id,name,class_name,role_name,attack,defense,health,speed,skill_power)
SELECT p.id,'Nyx Ardent','Assassin','Burst',151,64,450,82,88 FROM players p
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM operatives o WHERE o.player_id=p.id AND o.name='Nyx Ardent');

INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,awakened,is_core_weapon,weapon_family,talent_tree_key,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,o.id,'Gravemark Knuckles','Weapon','Epic',0,0,1,'Gravemark','core_blade','Attack',188,'[{"name":"Critical Damage","value":12},{"name":"Speed","value":6}]','{"base":"demo/gravemark-knuckles.png","layers":["rarity-epic"]}',3
FROM players p JOIN operatives o ON o.player_id=p.id AND o.name='Nyx Ardent'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Gravemark Knuckles');
INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,o.id,'Obsidian Mantle','Armor','Rare',0,'Defense',118,'[{"name":"Health","value":90},{"name":"Resistance","value":5}]','{"base":"demo/obsidian-mantle.png","layers":["rarity-rare"]}',2
FROM players p JOIN operatives o ON o.player_id=p.id AND o.name='Rook Vale'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Obsidian Mantle');
INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,o.id,'Nullglass Sigil','Accessory','Epic',0,'Skill Power',72,'[{"name":"Accuracy","value":8},{"name":"Essence Recovery","value":5}]','{"base":"demo/nullglass-sigil.png","layers":["rarity-epic"]}',3
FROM players p WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Nullglass Sigil');

INSERT INTO weapon_talent_nodes (talent_tree_key,rarity,node_key,node_name,description,stat_mod_json,unlock_cost) VALUES
('core_blade','Epic','grave_step','Grave Step','After a critical hit, gain a short burst of Speed.','{"speed":12,"critical_damage":8}',4),
('core_blade','Legendary','execution_line','Execution Line','Targets below half health take increased weapon damage.','{"attack_percent":10,"critical_damage":20}',7),
('core_blade','Mythic','memory_bite','Memory Bite','Rare world events can permanently alter the weapon\'s next enhancement.','{"attack_percent":15,"skill_power":55}',10)
ON DUPLICATE KEY UPDATE node_name=VALUES(node_name),description=VALUES(description),stat_mod_json=VALUES(stat_mod_json),unlock_cost=VALUES(unlock_cost);

INSERT INTO world_events (player_id,event_type,title,summary,payload_json,global_visibility)
SELECT p.id,'world_intro','The First Night','The Shadow network is active. Every contract, failure and discovery can leave a permanent mark.','{"kind":"intro","chapter":1}',1
FROM players p WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM world_events w WHERE w.event_type='world_intro');
INSERT INTO world_events (player_id,event_type,title,summary,payload_json,global_visibility)
SELECT p.id,'intel','A Cell Has Been Assembled','Night Cell is now operational. Its first contracts are waiting in the dark.','{"squad":"Night Cell","status":"ready"}',0
FROM players p WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM world_events w WHERE w.event_type='intel');
