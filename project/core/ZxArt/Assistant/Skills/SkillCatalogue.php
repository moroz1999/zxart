<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Skills;

use LogicException;

/**
 * The skills of the assistant: a Markdown file each, `<name>.md`, opened by a
 * frontmatter of `name` (the file's name) and `description`; the rest of the
 * file is the instructions. A file that does not follow this is a
 * `LogicException`, so a broken skill is found by the tests, not by an editor.
 *
 * @psalm-api
 */
final readonly class SkillCatalogue
{
    private const string FRONTMATTER = '/\A---\R(.*?)\R---\R(.*)\z/s';

    public function __construct(
        private string $directory,
    ) {
    }

    /**
     * @return list<SkillDto> by name
     */
    public function all(): array
    {
        $files = glob(rtrim($this->directory, '/') . '/*.md');
        if ($files === false) {
            return [];
        }
        sort($files);

        return array_map($this->read(...), $files);
    }

    public function get(string $name): SkillDto
    {
        $skills = $this->all();
        foreach ($skills as $skill) {
            if ($skill->name === $name) {
                return $skill;
            }
        }
        $names = implode(', ', array_map(static fn(SkillDto $skill): string => $skill->name, $skills));

        throw new UnknownSkillException("There is no skill \"{$name}\"; the skills are: {$names}");
    }

    private function read(string $file): SkillDto
    {
        $fileName = basename($file);
        if (preg_match(self::FRONTMATTER, (string)file_get_contents($file), $parts) !== 1) {
            throw new LogicException("The skill {$fileName} does not open with a frontmatter");
        }
        $fields = [];
        foreach (preg_split('/\R/', $parts[1]) ?: [] as $line) {
            [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
            $fields[trim($key)] = trim($value);
        }
        $name = $fields['name'] ?? '';
        $description = $fields['description'] ?? '';
        if ($name !== basename($file, '.md')) {
            throw new LogicException("The skill {$fileName} must be named as its file");
        }
        if ($description === '') {
            throw new LogicException("The skill {$fileName} has no description");
        }

        return new SkillDto($name, $description, trim($parts[2]));
    }
}
