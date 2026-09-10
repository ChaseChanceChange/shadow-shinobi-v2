CREATE TABLE IF NOT EXISTS legendary_weapons (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  weapon_key VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(128) NOT NULL,
  lore_text TEXT NOT NULL,
  talent_tree_json JSON NOT NULL,
  shatter_fragments INT NOT NULL DEFAULT 5,
  fragment_ttl_days INT NOT NULL DEFAULT 7,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO legendary_weapons (weapon_key,name,lore_text,talent_tree_json,shatter_fragments,fragment_ttl_days)
VALUES (
  'kageboshi',
  'Kageboshi, the Shadow-Splitter',
  'The first Kageboshi was forged by a nameless smith who tried to cut a shadow in half. The blade succeeded, but the shadow it cut was his own. It whispers memories rather than words.',
  '[{"tier":1,"key":"severed_reflection","name":"Severed Reflection"},{"tier":2,"key":"memory_theft","name":"Memory Theft"},{"tier":3,"key":"shadow_cut","name":"The Shadow Cut"},{"tier":4,"key":"kageboshi_echo","name":"Kageboshi’s Echo"}]',
  5,
  7
)
ON DUPLICATE KEY UPDATE lore_text=VALUES(lore_text),talent_tree_json=VALUES(talent_tree_json),shatter_fragments=VALUES(shatter_fragments),fragment_ttl_days=VALUES(fragment_ttl_days);

-- Lost Relics retain world placement and expiry metadata in their payload_json.
-- This migration intentionally avoids altering the existing lost_relics schema so old dev volumes remain compatible.
