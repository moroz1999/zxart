import {
  AssistantChangePreview,
  AssistantMention,
  AssistantMessage,
  AssistantPlan,
  AssistantPlanAction,
  AssistantRun,
} from '../../../shared/models/assistant-data';
import {
  AssistantChangeView,
  AssistantMessageView,
  AssistantPlanActionView,
  AssistantPlanView,
  AssistantSegment,
  AssistantView,
} from '../models/assistant-view';
import {AssistantMarkdownRenderer} from './assistant-markdown';

export interface AssistantViewInput {
  conversationId: number | null;
  messages: AssistantMessage[];
  plans: Record<number, AssistantPlan>;
  refs: Record<string, AssistantMention>;
  activeRun: AssistantRun | null;
  error: string | null;
}

const MENTION = /\[\[(\w+):(\d+)]]/g;
const DESTRUCTIVE_KINDS = ['delete', 'join', 'deleteFile'];
const EMPTY = '—';

/** `[[type:id]]` in a text, as links to the entities the refs know. */
export function mentionSegments(text: string, refs: Record<string, AssistantMention>): AssistantSegment[] {
  const segments: AssistantSegment[] = [];
  let last = 0;
  for (const match of text.matchAll(MENTION)) {
    const index = match.index ?? 0;
    if (index > last) {
      segments.push({kind: 'text', text: text.slice(last, index)});
    }
    segments.push(entitySegment(match[1], Number(match[2]), refs));
    last = index + match[0].length;
  }
  if (last < text.length || segments.length === 0) {
    segments.push({kind: 'text', text: text.slice(last)});
  }
  return segments;
}

export function buildAssistantView(input: AssistantViewInput, markdown: AssistantMarkdownRenderer | null = null): AssistantView {
  const running = input.activeRun !== null;
  return {
    conversationId: input.conversationId,
    hasConversation: input.conversationId !== null,
    messages: input.messages.map(message => messageView(message, input, running, markdown)),
    running,
    error: input.error,
  };
}

function messageView(
  message: AssistantMessage,
  input: AssistantViewInput,
  running: boolean,
  markdown: AssistantMarkdownRenderer | null,
): AssistantMessageView {
  const payload = message.payload;
  const planId = message.kind === 'plan' && typeof payload?.['planId'] === 'number' ? payload['planId'] : null;
  const plan = planId !== null ? input.plans[planId] : undefined;
  return {
    id: message.id,
    role: message.role,
    kind: message.kind,
    segments: mentionSegments(message.text, input.refs),
    html: markdown !== null && message.kind === 'text' && message.role === 'assistant' ? markdown(message.text, input.refs) : null,
    toolName: message.kind === 'toolCall' && typeof payload?.['tool'] === 'string' ? payload['tool'] : null,
    details: plan ? JSON.stringify(plan, null, 2) : payload !== null ? JSON.stringify(payload, null, 2) : null,
    failed: payload?.['failed'] === true,
    plan: plan ? planView(plan, input.refs, running) : null,
  };
}

function planView(plan: AssistantPlan, refs: Record<string, AssistantMention>, running: boolean): AssistantPlanView {
  const actions = plan.actions.map((action, index) => actionView(action, index + 1, refs));
  return {
    id: plan.id,
    title: plan.title,
    status: plan.status,
    canApprove: plan.status === 'proposed' && !running,
    destructiveCount: actions.filter(action => action.changeKind !== null && DESTRUCTIVE_KINDS.includes(action.changeKind)).length,
    actions,
  };
}

function actionView(action: AssistantPlanAction, number: number, refs: Record<string, AssistantMention>): AssistantPlanActionView {
  const preview: AssistantChangePreview | null = action.preview;
  return {
    number,
    tool: action.tool,
    changeKind: preview?.kind ?? null,
    target: preview ? targetSegments(preview, refs) : [],
    targetCreatedInStep: preview?.target.createdInStep ?? null,
    changes: preview ? preview.changes.map(change => changeView(change, refs)) : [],
    consequences: preview?.consequences ?? [],
    dependsOn: action.dependsOn,
    status: action.status,
    error: action.error,
  };
}

function targetSegments(preview: AssistantChangePreview, refs: Record<string, AssistantMention>): AssistantSegment[] {
  const target = preview.target;
  if (target.id !== null && refs[`${target.type}:${target.id}`]) {
    return [entitySegment(target.type, target.id, refs)];
  }
  return [{kind: 'text', text: target.title}];
}

function changeView(change: {field: string; before: unknown; after: unknown}, refs: Record<string, AssistantMention>): AssistantChangeView {
  return {field: change.field, before: valueSegments(change.before, refs), after: valueSegments(change.after, refs)};
}

function valueSegments(value: unknown, refs: Record<string, AssistantMention>): AssistantSegment[] {
  if (value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
    return [{kind: 'text', text: EMPTY}];
  }
  if (Array.isArray(value)) {
    return value.flatMap((item, index) => index === 0 ? valueSegments(item, refs) : [{kind: 'text', text: ', '} as AssistantSegment, ...valueSegments(item, refs)]);
  }
  if (isEntityValue(value)) {
    return [entitySegment(value.type, value.id, refs)];
  }
  if (typeof value === 'object') {
    return [{kind: 'text', text: JSON.stringify(value)}];
  }
  return [{kind: 'text', text: String(value)}];
}

function isEntityValue(value: unknown): value is {type: string; id: number} {
  return typeof value === 'object' && value !== null
    && typeof (value as Record<string, unknown>)['type'] === 'string'
    && typeof (value as Record<string, unknown>)['id'] === 'number'
    && Object.keys(value).length === 2;
}

function entitySegment(type: string, id: number, refs: Record<string, AssistantMention>): AssistantSegment {
  const ref = refs[`${type}:${id}`];
  return ref ? {kind: 'link', text: ref.title, url: ref.url} : {kind: 'text', text: `${type} #${id}`};
}
