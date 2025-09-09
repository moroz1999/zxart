CREATE TABLE engine_ip_bans (
                         ip VARCHAR(45) NOT NULL,
                         reason ENUM('honeypot','manual','abuse','ua') NOT NULL DEFAULT 'honeypot',
                         user_agent VARCHAR(512) NULL,
                         path VARCHAR(1024) NULL,
                         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;