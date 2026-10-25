import {expect} from 'vitest';

/** The JSON request and the files of a body built by `entitySaveBody`. */
export async function readSaveBody(body: unknown): Promise<{data: unknown; files: FormData}> {
  expect(body).toBeInstanceOf(FormData);
  const form = body as FormData;
  return {data: JSON.parse(await (form.get('data') as Blob).text()), files: form};
}
