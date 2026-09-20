/**
 * A failure the person is meant to read, rather than a bug: the release is
 * packed in a format the browser cannot open, or it is too big for the card.
 * The dialog translates the key instead of showing the message.
 */
export class EmulatorError extends Error {
  constructor(
    readonly translationKey: string,
    readonly params?: Record<string, unknown>,
  ) {
    super(translationKey);
    this.name = 'EmulatorError';
  }
}
