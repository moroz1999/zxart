-- Radio criteria are no longer persisted; stored user values cascade away with the preference row
DELETE FROM engine_preferences WHERE code = 'radio_criteria';
