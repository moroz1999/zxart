import type {Marked as MarkedClass, Tokens} from 'marked';
import {AssistantMention} from '../../../shared/models/assistant-data';

/** Turns the assistant's Markdown into HTML, with the entities it mentions as links. */
export type AssistantMarkdownRenderer = (text: string, refs: Record<string, AssistantMention>) => string;

const MENTION = /\[\[(\w+):(\d+)]]/g;

/**
 * The renderer over the `marked` module, which is loaded lazily. A mention the
 * refs know becomes a link to the entity's page, an unknown one plain text; an
 * outside link opens in a new tab. The HTML is sanitized where it is bound.
 */
export function createAssistantMarkdown(markedModule: {Marked: typeof MarkedClass}): AssistantMarkdownRenderer {
  const marked = new markedModule.Marked({
    renderer: {
      link(this: {parser: {parseInline(tokens: Tokens.Generic[]): string}}, token: Tokens.Link): string {
        const text = this.parser.parseInline(token.tokens);
        const outside = /^https?:\/\//i.test(token.href);
        return `<a href="${escapeAttribute(token.href)}"${outside ? ' target="_blank" rel="noopener"' : ''}>${text}</a>`;
      },
    },
  });
  return (text, refs) => marked.parse(withMentionLinks(text, refs), {async: false});
}

function withMentionLinks(text: string, refs: Record<string, AssistantMention>): string {
  return text.replace(MENTION, (_match, type: string, id: string) => {
    const ref = refs[`${type}:${id}`];
    return ref ? `[${ref.title.replace(/[[\]\\]/g, '\\$&')}](${ref.url})` : `${type} #${id}`;
  });
}

function escapeAttribute(value: string): string {
  return value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
}
