import {readFileSync} from 'node:fs';
import {resolve} from 'node:path';
import {describe, expect, it} from 'vitest';

describe('player theme', () => {
  it('defines its variables on the document root', () => {
    const source = readFileSync(resolve('src/app/shared/theme/_zx-player.theme.scss'), 'utf8');

    expect(source).not.toContain('\uFEFF');
    expect(source).toMatch(/(?:^|\n):root\s*\{/);
  });
});
