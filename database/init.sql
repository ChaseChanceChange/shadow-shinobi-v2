CREATE TABLE IF NOT EXISTS players (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(32) NOT NULL UNIQUE,
  display_name VARCHAR(64) NOT NULL,
  coins BIGINT NOT NULL DEFAULT 1000,
  gear_fragments BIGINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS operatives (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  class_name VARCHAR(32) NOT NULL,
  role_name VARCHAR(32) NOT NULL,
  level INT NOT NULL DEFAULT 1,
  experience INT NOT NULL DEFAULT 0,
  attack INT NOT NULL DEFAULT 100,
  defense INT NOT NULL DEFAULT 80,
  health INT NOT NULL DEFAULT 500,
  speed INT NOT NULL DEFAULT 50,
  critical_rate DECIMAL(6,3) NOT NULL DEFAULT 5.000,
  critical_damage DECIMAL(7,3) NOT NULL DEFAULT 150.000,
  accuracy DECIMAL(6,3) NOT NULL DEFAULT 90.000,
  resistance DECIMAL(6,3) NOT NULL DEFAULT 10.000,
  skill_power INT NOT NULL DEFAULT 50,
  essence_capacity INT NOT NULL DEFAULT 100,
  essence_recovery INT NOT NULL DEFAULT 5,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_operative_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS squads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL DEFAULT 'Night Cell',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_squad_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS squad_members (
  squad_id BIGINT UNSIGNED NOT NULL,
  operative_id BIGINT UNSIGNED NOT NULL,
  slot_index INT NOT NULL,
  PRIMARY KEY (squad_id, operative_id),
  UNIQUE KEY uq_squad_slot (squad_id, slot_index),
  CONSTRAINT fk_member_squad FOREIGN KEY (squad_id) REFERENCES squads(id) ON DELETE CASCADE,
  CONSTRAINT fk_member_operative FOREIGN KEY (operative_id) REFERENCES operatives(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NULL,
  operative_id BIGINT UNSIGNED NULL,
  item_name VARCHAR(128) NOT NULL,
  slot_name VARCHAR(32) NOT NULL,
  rarity VARCHAR(32) NOT NULL,
  enhancement_level INT NOT NULL DEFAULT 0,
  awakened TINYINT(1) NOT NULL DEFAULT 0,
  is_core_weapon TINYINT(1) NOT NULL DEFAULT 0,
  weapon_family VARCHAR(64) NULL,
  talent_tree_key VARCHAR(64) NULL,
  main_stat_name VARCHAR(32) NOT NULL,
  main_stat_value DECIMAL(12,3) NOT NULL,
  substats_json JSON NOT NULL,
  visual_json JSON NOT NULL,
  set_value INT NOT NULL DEFAULT 1,
  destroyed TINYINT(1) NOT NULL DEFAULT 0,
  origin_item_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_equipment_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
  CONSTRAINT fk_equipment_operative FOREIGN KEY (operative_id) REFERENCES operatives(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gear_fragments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NOT NULL,
  source_item_id BIGINT UNSIGNED NULL,
  quantity INT NOT NULL,
  reason VARCHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fragment_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lost_relics (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NULL,
  name VARCHAR(128) NOT NULL,
  source_item_id BIGINT UNSIGNED NULL,
  rarity VARCHAR(32) NOT NULL,
  power_rating INT NOT NULL,
  payload_json JSON NOT NULL,
  discovered TINYINT(1) NOT NULL DEFAULT 0,
  discovered_by_player_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  discovered_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_relic_owner FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
  CONSTRAINT fk_relic_discoverer FOREIGN KEY (discovered_by_player_id) REFERENCES players(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS world_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(64) NOT NULL,
  title VARCHAR(160) NOT NULL,
  summary TEXT NOT NULL,
  payload_json JSON NOT NULL,
  global_visibility TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_world_event_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
  INDEX idx_world_event_player_time (player_id, created_at),
  INDEX idx_world_event_global_time (global_visibility, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS missions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mission_key VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(128) NOT NULL,
  description TEXT NOT NULL,
  difficulty VARCHAR(32) NOT NULL,
  recommended_level INT NOT NULL DEFAULT 1,
  reward_coins INT NOT NULL DEFAULT 0,
  loot_json JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mission_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NOT NULL,
  mission_id BIGINT UNSIGNED NOT NULL,
  result VARCHAR(16) NOT NULL,
  reward_json JSON NOT NULL,
  completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_run_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
  CONSTRAINT fk_run_mission FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS training_jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  operative_id BIGINT UNSIGNED NOT NULL,
  training_type VARCHAR(64) NOT NULL,
  stat_name VARCHAR(32) NOT NULL,
  gain_amount INT NOT NULL,
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completes_at TIMESTAMP NOT NULL,
  completed TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_training_operative FOREIGN KEY (operative_id) REFERENCES operatives(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS living_world_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id BIGINT UNSIGNED NOT NULL,
  world_seed BIGINT NOT NULL,
  ticks INT NOT NULL DEFAULT 0,
  status VARCHAR(16) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_living_run_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS living_world_entities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  run_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(96) NOT NULL,
  species VARCHAR(48) NOT NULL,
  position_x INT NOT NULL,
  position_y INT NOT NULL,
  energy INT NOT NULL DEFAULT 50,
  status VARCHAR(16) NOT NULL DEFAULT 'alive',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_living_entity_run FOREIGN KEY (run_id) REFERENCES living_world_runs(id) ON DELETE CASCADE,
  INDEX idx_living_entity_run_status (run_id,status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS weapon_talent_nodes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  talent_tree_key VARCHAR(64) NOT NULL,
  rarity VARCHAR(32) NOT NULL,
  node_key VARCHAR(64) NOT NULL,
  node_name VARCHAR(128) NOT NULL,
  description TEXT NOT NULL,
  stat_mod_json JSON NOT NULL,
  unlock_cost INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_talent_node (talent_tree_key, node_key)
) ENGINE=InnoDB;

INSERT INTO players (username,display_name,coins) VALUES ('demo','Shadow Commander',2500)
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name), coins=GREATEST(players.coins,VALUES(coins));

INSERT INTO squads (player_id,name)
SELECT id,'Night Cell' FROM players WHERE username='demo'
AND NOT EXISTS (SELECT 1 FROM squads s WHERE s.player_id=players.id LIMIT 1);

INSERT INTO operatives (player_id,name,class_name,role_name,attack,defense,health,speed,skill_power)
SELECT id,'Kael Veyr','Striker','Damage',125,80,540,58,70 FROM players WHERE username='demo'
AND NOT EXISTS (SELECT 1 FROM operatives o JOIN players p ON p.id=o.player_id WHERE p.username='demo' AND o.name='Kael Veyr');
INSERT INTO operatives (player_id,name,class_name,role_name,attack,defense,health,speed,skill_power)
SELECT id,'Mira Ashfall','Medic','Support',72,96,480,62,92 FROM players WHERE username='demo'
AND NOT EXISTS (SELECT 1 FROM operatives o JOIN players p ON p.id=o.player_id WHERE p.username='demo' AND o.name='Mira Ashfall');

INSERT INTO squad_members (squad_id,operative_id,slot_index)
SELECT s.id,o.id,0 FROM squads s JOIN players p ON p.id=s.player_id JOIN operatives o ON o.player_id=p.id AND o.name='Kael Veyr'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM squad_members sm WHERE sm.squad_id=s.id AND sm.operative_id=o.id);
INSERT INTO squad_members (squad_id,operative_id,slot_index)
SELECT s.id,o.id,1 FROM squads s JOIN players p ON p.id=s.player_id JOIN operatives o ON o.player_id=p.id AND o.name='Mira Ashfall'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM squad_members sm WHERE sm.squad_id=s.id AND sm.operative_id=o.id);

INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,awakened,is_core_weapon,weapon_family,talent_tree_key,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,o.id,'Voidfang Core Blade','Weapon','Rare',0,0,1,'Voidfang','core_blade','Attack',145,'[{"name":"Critical Rate","value":6},{"name":"Speed","value":4}]','{"base":"demo/core-blade.png","layers":["rarity-rare","enhancement-0"]}',2
FROM players p JOIN operatives o ON o.player_id=p.id AND o.name='Kael Veyr'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Voidfang Core Blade');
INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,o.id,'Ironwrought Carapace','Armor','Uncommon',0,'Defense',72,'[{"name":"Health","value":60}]','{"base":"demo/armor.png","layers":["rarity-uncommon"]}',1
FROM players p JOIN operatives o ON o.player_id=p.id AND o.name='Kael Veyr'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Ironwrought Carapace');
INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,NULL,'Stormglass Charm','Accessory','Rare',0,'Skill Power',35,'[{"name":"Accuracy","value":5}]','{"base":"demo/charm.png","layers":["rarity-rare"]}',1
FROM players p WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Stormglass Charm');
INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,NULL,'Ashline Edge','Weapon','Common',0,'Attack',42,'[{"name":"Speed","value":2}]','{"base":"demo/weapon.png","layers":["rarity-common"]}',1
FROM players p WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Ashline Edge');

INSERT INTO equipment_items (player_id,operative_id,item_name,slot_name,rarity,enhancement_level,awakened,is_core_weapon,weapon_family,talent_tree_key,main_stat_name,main_stat_value,substats_json,visual_json,set_value)
SELECT p.id,o.id,'Moonvein Core Staff','Weapon','Rare',0,0,1,'Moonvein','core_blade','Skill Power',95,'[{"name":"Essence Recovery","value":7},{"name":"Health","value":35}]','{"base":"demo/core-staff.png","layers":["rarity-rare","enhancement-0"]}',2
FROM players p JOIN operatives o ON o.player_id=p.id AND o.name='Mira Ashfall'
WHERE p.username='demo' AND NOT EXISTS (SELECT 1 FROM equipment_items e WHERE e.player_id=p.id AND e.item_name='Moonvein Core Staff');

INSERT INTO missions (mission_key,name,description,difficulty,recommended_level,reward_coins,loot_json) VALUES
('first-contract','Ashline Intercept','Intercept a courier moving through the Ashline route.','Normal',1,180,'[{"type":"material","name":"Refined Alloy","quantity":2}]'),
('nightfall-recovery','Nightfall Recovery','Recover a sealed case from a ruined relay station.','Hard',2,320,'[{"type":"material","name":"Void Shard","quantity":1}]'),
('hollow-judge','The Hollow Judge','Face a dangerous combatant whose name has become a warning.','Brutal',5,900,'[{"type":"gear","rarity":"Epic","quantity":1}]')
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),difficulty=VALUES(difficulty),recommended_level=VALUES(recommended_level),reward_coins=VALUES(reward_coins),loot_json=VALUES(loot_json);

INSERT INTO weapon_talent_nodes (talent_tree_key,rarity,node_key,node_name,description,stat_mod_json,unlock_cost) VALUES
('core_blade','Common','edge','Razor Memory','Gain a small Attack bonus.','{"attack":15}',1),
('core_blade','Rare','void_step','Void Step','Gain Speed after acting.','{"speed":8}',2),
('core_blade','Epic','night_surge','Night Surge','Critical hits can trigger a bonus strike.','{"critical_rate":7,"skill_power":20}',3),
('core_blade','Legendary','blackstar','Blackstar','Unlock an advanced weapon passive.','{"attack_percent":8,"critical_damage":15}',5),
('core_blade','Mythic','world_echo','World Echo','The weapon can interact with World Memory events.','{"attack_percent":12,"skill_power":40}',8)
ON DUPLICATE KEY UPDATE node_name=VALUES(node_name),description=VALUES(description),stat_mod_json=VALUES(stat_mod_json),unlock_cost=VALUES(unlock_cost);
