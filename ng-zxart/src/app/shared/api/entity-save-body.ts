/** A file part, a list of them, or lists by kind. */
export type SaveBodyFiles = Record<string, File | File[] | Partial<Record<string, File[]>> | null | undefined>;

/**
 * The multipart body of an entity create or update: the request as the JSON
 * `data` part, a single file under its name, a list under `name[]`, lists by
 * kind under `name[kind][]`. Files not given are left out.
 */
export function entitySaveBody(request: object, files: SaveBodyFiles): FormData {
  const body = new FormData();
  body.append('data', new Blob([JSON.stringify(request)], {type: 'application/json'}));
  for (const [name, value] of Object.entries(files)) {
    if (value instanceof File) {
      body.append(name, value, value.name);
    } else if (Array.isArray(value)) {
      for (const file of value) {
        body.append(`${name}[]`, file, file.name);
      }
    } else if (value) {
      for (const [kind, kindFiles] of Object.entries(value)) {
        for (const file of kindFiles ?? []) {
          body.append(`${name}[${kind}][]`, file, file.name);
        }
      }
    }
  }
  return body;
}
