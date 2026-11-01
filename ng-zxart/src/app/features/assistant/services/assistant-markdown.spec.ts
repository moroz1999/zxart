import * as markedModule from 'marked';
import {describe, expect, it} from 'vitest';
import {createAssistantMarkdown} from './assistant-markdown';

const refs = {'prod:5': {title: 'Elite [1985]', url: '/prod/5'}};
const render = createAssistantMarkdown(markedModule);

describe('createAssistantMarkdown', () => {
  it('renders Markdown with known mentions as links to their pages', () => {
    expect(render('**Found** [[prod:5]] and [[author:9]]', refs))
      .toBe('<p><strong>Found</strong> <a href="/prod/5">Elite [1985]</a> and author #9</p>\n');
  });

  it('opens an outside link in a new tab', () => {
    expect(render('[zxdb](https://spectrumcomputing.co.uk/)', {}))
      .toBe('<p><a href="https://spectrumcomputing.co.uk/" target="_blank" rel="noopener">zxdb</a></p>\n');
  });
});
