-- Shadow Shinobi v2 :: Persistent Overworld
-- Runtime also creates this table defensively so existing development databases do not require a reset.
CREATE TABLE IF NOT EXISTS world_player_state (
    player_id BIGINT UNSIGNED PRIMARY KEY,
    map_key VARCHAR(64) NOT NULL DEFAULT 'ashen-frontier',
    position_x INT NOT NULL DEFAULT 10,
    position_y INT NOT NULL DEFAULT 45,
    energy INT NOT NULL DEFAULT 100,
    steps BIGINT UNSIGNED NOT NULL DEFAULT 0,
    discovered_json JSON NOT NULL,
    last_move_at DATETIME(6) NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_world_player_state_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
