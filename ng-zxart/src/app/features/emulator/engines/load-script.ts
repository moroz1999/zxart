/** State of one script URL, so an engine loads its runtime only once per page. */
export interface ScriptState {
  injected: boolean;
}

export function loadScriptOnce(state: ScriptState, src: string): Promise<void> {
  if (state.injected) {
    return Promise.resolve();
  }
  return new Promise<void>((resolve, reject) => {
    const script = document.createElement('script');
    script.src = src;
    script.onload = () => {
      state.injected = true;
      resolve();
    };
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}
