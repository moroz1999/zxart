<?php
declare(strict_types=1);

namespace ZxArt\Import;

/**
 * Author roles ZxDB credits a label with, as stored in its `roletypes` table.
 * The set is closed: `roles.roletype_id` is a foreign key to those ten codes.
 */
enum ZxdbRoleType: string
{
    case Code = 'C';
    case GameDesign = 'D';
    case InGameGraphics = 'G';
    case InlayArt = 'A';
    case LevelDesign = 'V';
    case LoadScreen = 'S';
    case Localization = 'T';
    case Music = 'M';
    case SoundEffects = 'X';
    case StoryWriting = 'W';

    public function authorshipRole(): string
    {
        return match ($this) {
            self::Code => 'code',
            self::GameDesign => 'gamedesign',
            self::InGameGraphics => 'graphics',
            self::InlayArt => 'illustrating',
            self::LevelDesign => 'leveldesign',
            self::LoadScreen => 'loading_screen',
            self::Localization => 'localization',
            self::Music => 'music',
            self::SoundEffects => 'sfx',
            self::StoryWriting => 'story',
        };
    }
}
