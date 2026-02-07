-- Homepage layout preferences
INSERT INTO engine_preferences (code, type) VALUES ('homepage_order', 'string');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_disabled', 'string');

-- Homepage per-module limits
INSERT INTO engine_preferences (code, type) VALUES ('homepage_new_prods_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_new_pictures_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_new_tunes_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_best_demos_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_best_games_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_recent_parties_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_best_pictures_month_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_latest_prods_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_latest_releases_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_support_prods_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_unvoted_pictures_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_random_pictures_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_unvoted_tunes_limit', 'integer');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_random_tunes_limit', 'integer');

-- Homepage per-module minRating
INSERT INTO engine_preferences (code, type) VALUES ('homepage_new_prods_min_rating', 'string');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_best_demos_min_rating', 'string');
INSERT INTO engine_preferences (code, type) VALUES ('homepage_best_games_min_rating', 'string');
