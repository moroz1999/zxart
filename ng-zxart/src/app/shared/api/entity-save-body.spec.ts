import {describe, expect, it} from 'vitest';
import {entitySaveBody} from './entity-save-body';
import {readSaveBody} from './read-save-body.testing';

describe('entitySaveBody', () => {
  it('carries the request as the JSON `data` part', async () => {
    const body = entitySaveBody({fields: {title: 'Goldmaster Writer'}, groups: []}, {});

    expect((await readSaveBody(body)).data).toEqual({fields: {title: 'Goldmaster Writer'}, groups: []});
    expect([...body.keys()]).toEqual(['data']);
  });

  it('adds a single file under its name and skips the ones not given', () => {
    const image = new File(['png'], 'logo.png');

    const body = entitySaveBody({fields: {}}, {image, file: null, other: undefined});

    expect(body.get('image')).toEqual(image);
    expect((body.get('image') as File).name).toBe('logo.png');
    expect(body.has('file')).toBe(false);
    expect(body.has('other')).toBe(false);
  });

  it('adds a list under `name[]` and lists by kind under `name[kind][]`', () => {
    const alpha = new File(['a'], 'alpha.tap');
    const beta = new File(['b'], 'beta.tap');
    const screen = new File(['s'], 'screen.png');
    const map = new File(['m'], 'map.png');

    const body = entitySaveBody({fields: {}}, {
      releaseFiles: [alpha, beta],
      files: {screenshots: [screen], maps: [map], inlays: []},
    });

    expect(body.getAll('releaseFiles[]')).toEqual([alpha, beta]);
    expect(body.getAll('files[screenshots][]')).toEqual([screen]);
    expect(body.getAll('files[maps][]')).toEqual([map]);
    expect(body.has('files[inlays][]')).toBe(false);
  });
});
