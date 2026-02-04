CREATE TABLE engine_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('string', 'boolean', 'integer') NOT NULL DEFAULT 'string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE engine_user_preference_values (
    user_id INT NOT NULL,
    preference_id INT NOT NULL,
    value VARCHAR(255) NOT NULL,
    PRIMARY KEY (user_id, preference_id),
    FOREIGN KEY (preference_id) REFERENCES engine_preferences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO engine_preferences (code, type) VALUES ('theme', 'string');

-- Note: In repositories use TABLE names WITHOUT 'engine_' prefix
-- because Illuminate\Database\Connection adds it automatically.
