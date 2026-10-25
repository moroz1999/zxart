import {expect} from 'vitest';

/** The JSON request and the files of a body built by `entitySaveBody`. */
export async function readSaveBody(body: unknown): Promise<{data: unknown; files: FormData}> {
  expect(body).toBeInstanceOf(FormData);
  const form = body as FormData;
  return {data: JSON.parse(await readText(form.get('data') as Blob)), files: form};
}

/** jsdom's Blob has no `text()`. */
function readText(blob: Blob): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = () => reject(reader.error);
    reader.readAsText(blob);
  });
}
