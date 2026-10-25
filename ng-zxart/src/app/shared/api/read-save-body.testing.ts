import {expect} from 'vitest';

/** The JSON request and the files of a body built by `entitySaveBody`. */
export function readSaveBody(body: unknown): {data: unknown; files: FormData} {
  expect(body).toBeInstanceOf(FormData);
  const form = body as FormData;
  return {data: JSON.parse(form.get('data') as string), files: form};
}
