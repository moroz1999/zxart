import {
  AssistantChangePreview,
  AssistantConversationSummary,
  AssistantMention,
  AssistantMessage,
  AssistantPlan,
  AssistantPlanAction,
  AssistantRun,
} from '../../../shared/models/assistant-data';
import {ZxStepStatus} from '../../../shared/ui/zx-steps/zx-step.component';
import {
  AssistantActivityStepView,
  AssistantChangeView,
  AssistantConversationGroup,
  AssistantMessageView,
  AssistantPlanActionView,
  AssistantPlanView,
  AssistantSegment,
  AssistantStreamItem,
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

/** A change that deletes or merges data away. */
export function isDestructiveKind(kind: string | null): boolean {
  return kind !== null && DESTRUCTIVE_KINDS.includes(kind);
}

/** The read tools that have a phrase of their own (`assistant.tool.<name>`). */
const PHRASED_TOOLS = new Set([
  'search', 'find_locations', 'find_parties', 'search_prods', 'search_releases', 'search_authors', 'search_groups',
  'find_tags', 'list_categories', 'list_hardware', 'list_options',
  'get_prod', 'get_release', 'get_author', 'get_author_alias', 'get_group', 'get_group_alias',
  'fetch_url', 'view_image', 'describe_write_tool', 'load_skill',
]);

/** The entity a `get_*` tool reads, by its `id` argument. */
const ENTITY_TOOLS: Readonly<Record<string, string>> = {
  'get_prod': 'prod',
  'get_release': 'release',
  'get_author': 'author',
  'get_author_alias': 'authorAlias',
  'get_group': 'group',
  'get_group_alias': 'groupAlias',
};

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
  const items: AssistantStreamItem[] = [];
  for (const message of input.messages) {
    if (message.kind === 'planProgress') {
      continue;
    }
    if (message.kind === 'toolCall') {
      const last = items[items.length - 1];
      const step = activityStep(message, input.refs);
      if (last?.kind === 'activity') {
        last.activity.steps.push(step);
      } else {
        items.push({kind: 'activity', activity: {id: message.id, steps: [step], failedCount: 0, running: false}});
      }
      continue;
    }
    items.push({kind: 'message', message: messageView(message, input, running, markdown)});
  }
  for (const item of items) {
    if (item.kind === 'activity') {
      item.activity.failedCount = item.activity.steps.filter(step => step.failed).length;
    }
  }
  const last = items[items.length - 1];
  if (running && last?.kind === 'activity') {
    last.activity.running = true;
  }
  return {
    conversationId: input.conversationId,
    hasConversation: input.conversationId !== null,
    items,
    running,
    error: input.error,
  };
}

/** The conversations by the day of their last activity, in their order; days without any are left out. */
export function groupConversations(conversations: AssistantConversationSummary[], now: Date): AssistantConversationGroup[] {
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime() / 1000;
  const yesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1).getTime() / 1000;
  const groups: AssistantConversationGroup[] = [
    {day: 'today', conversations: []},
    {day: 'yesterday', conversations: []},
    {day: 'earlier', conversations: []},
  ];
  for (const conversation of conversations) {
    const index = conversation.lastMessageAt >= today ? 0 : conversation.lastMessageAt >= yesterday ? 1 : 2;
    groups[index].conversations.push(conversation);
  }
  return groups.filter(group => group.conversations.length > 0);
}

function activityStep(message: AssistantMessage, refs: Record<string, AssistantMention>): AssistantActivityStepView {
  const payload = message.payload ?? {};
  const tool = typeof payload['tool'] === 'string' ? payload['tool'] : message.text;
  return {
    id: message.id,
    tool,
    labelKey: `assistant.tool.${PHRASED_TOOLS.has(tool) ? tool : 'default'}`,
    subject: toolSubject(tool, typeof payload['arguments'] === 'string' ? payload['arguments'] : '', refs),
    failed: payload['failed'] === true,
    details: JSON.stringify(payload, null, 2),
  };
}

/** What a call was about, read from its arguments. */
function toolSubject(tool: string, json: string, refs: Record<string, AssistantMention>): string | null {
  let args: unknown;
  try {
    args = JSON.parse(json);
  } catch {
    return null;
  }
  if (typeof args !== 'object' || args === null) {
    return null;
  }
  const record = args as Record<string, unknown>;
  const filter = record['filter'];
  const filterTitle = typeof filter === 'object' && filter !== null ? (filter as Record<string, unknown>)['title'] : null;
  for (const value of [record['phrase'], record['url'], record['name'], filterTitle]) {
    if (typeof value === 'string' && value !== '') {
      return value;
    }
  }
  const id = record['id'] ?? record['fileId'];
  if (typeof id === 'number') {
    const entityType = ENTITY_TOOLS[tool];
    return (entityType ? refs[`${entityType}:${id}`]?.title : undefined) ?? `#${id}`;
  }
  return null;
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
    details: plan ? JSON.stringify(plan, null, 2) : payload !== null ? JSON.stringify(payload, null, 2) : null,
    plan: plan ? planView(plan, input.refs, running) : null,
  };
}

function planView(plan: AssistantPlan, refs: Record<string, AssistantMention>, running: boolean): AssistantPlanView {
  const failedIndex = plan.actions.findIndex(action => action.status === 'failed');
  const stoppedAt = failedIndex === -1 ? null : failedIndex + 1;
  const runningIndex = plan.status === 'executing' ? plan.actions.findIndex(action => action.status === 'pending') : -1;
  const targets = plan.actions.map(action => actionTarget(action, refs));
  const stepRef = (step: number): AssistantSegment[] => {
    const target = targets[step - 1] ?? [];
    return target.length === 1 && target[0].kind === 'link'
      ? target
      : [{kind: 'step', step, text: target.map(segment => segment.kind === 'image' ? '' : segment.text).join('') || `#${step}`}];
  };
  const actions = plan.actions.map((action, index) => actionView(action, index + 1, targets[index], refs, stepRef, index === runningIndex, stoppedAt));
  const waiting = plan.status === 'proposed' && !running;
  return {
    id: plan.id,
    title: plan.title,
    status: plan.status,
    canApprove: waiting,
    canCancel: waiting,
    destructiveCount: actions.filter(action => isDestructiveKind(action.changeKind)).length,
    doneCount: plan.actions.filter(action => action.status === 'done').length,
    stoppedAt,
    actions,
  };
}

type StepRef = (step: number) => AssistantSegment[];

function actionView(
  action: AssistantPlanAction,
  number: number,
  target: AssistantSegment[],
  refs: Record<string, AssistantMention>,
  stepRef: StepRef,
  isRunning: boolean,
  stoppedAt: number | null,
): AssistantPlanActionView {
  const preview: AssistantChangePreview | null = action.preview;
  return {
    number,
    tool: action.tool,
    marker: actionMarker(action, isRunning),
    entityType: preview?.target.type ?? null,
    changeKind: preview?.kind ?? null,
    target,
    targetCreatedInStep: preview?.target.createdInStep ?? null,
    changes: preview ? preview.changes.map(change => changeView(change, refs)) : argumentRows(action.arguments, refs, stepRef),
    consequences: preview?.consequences ?? [],
    dependsOn: action.dependsOn,
    status: action.status,
    waitsFor: action.status === 'skipped' ? stoppedAt : null,
    error: action.error,
  };
}

/** What the action changes: the preview's target, else the title its fields give. */
function actionTarget(action: AssistantPlanAction, refs: Record<string, AssistantMention>): AssistantSegment[] {
  if (action.preview) {
    const createdId = typeof action.result?.['id'] === 'number' ? action.result['id'] : null;
    return targetSegments(action.preview, createdId, refs);
  }
  const fields = parseArguments(action.arguments)['fields'];
  const title = typeof fields === 'object' && fields !== null ? (fields as Record<string, unknown>)['title'] : null;
  return typeof title === 'string' && title !== '' ? [{kind: 'text', text: title}] : [];
}

/** An unpreviewed action's arguments as rows; `fields` is opened into its own. */
function argumentRows(json: string, refs: Record<string, AssistantMention>, stepRef: StepRef): AssistantChangeView[] {
  const args = parseArguments(json);
  const fields = args['fields'];
  const entries = Object.entries(args).flatMap(([key, value]) => key === 'fields' && typeof fields === 'object' && fields !== null && !Array.isArray(fields)
    ? Object.entries(fields as Record<string, unknown>)
    : [[key, value] as [string, unknown]]);
  return entries.map(([field, value]) => ({field, before: [], after: argumentSegments(value, ARGUMENT_ID.exec(field)?.[1] ?? null, refs, stepRef)}));
}

/** An argument named `<type>Id` or `<type>Ids` gives entities of that type by id. */
const ARGUMENT_ID = /^(\w+?)Ids?$/;
const IMAGE_URL = /^https?:\/\/[^?#]+\.(png|jpe?g|gif|webp)([?#].*)?$/i;

/** An argument's value: an entity named by id with its title and id, an image as an image, the rest as values are. */
function argumentSegments(value: unknown, entityType: string | null, refs: Record<string, AssistantMention>, stepRef: StepRef): AssistantSegment[] {
  if (Array.isArray(value) && value.length > 0) {
    const parts = value.map(item => argumentSegments(item, entityType, refs, stepRef));
    return parts.flatMap((part, index) => index === 0 || (isImage(part) && isImage(parts[index - 1]))
      ? part
      : [{kind: 'text', text: ', '} as AssistantSegment, ...part]);
  }
  if (entityType !== null && typeof value === 'number') {
    const ref = refs[`${entityType}:${value}`];
    return [ref ? {kind: 'link', text: `${ref.title} #${value}`, url: ref.url} : {kind: 'text', text: `${entityType} #${value}`}];
  }
  if (typeof value === 'string' && IMAGE_URL.test(value)) {
    return [{kind: 'image', url: value}];
  }
  return valueSegments(value, refs, stepRef);
}

function isImage(segments: AssistantSegment[]): boolean {
  return segments.length === 1 && segments[0].kind === 'image';
}

function parseArguments(json: string): Record<string, unknown> {
  try {
    const parsed: unknown = JSON.parse(json);
    return typeof parsed === 'object' && parsed !== null && !Array.isArray(parsed) ? parsed as Record<string, unknown> : {};
  } catch {
    return {};
  }
}

function actionMarker(action: AssistantPlanAction, isRunning: boolean): ZxStepStatus {
  if (isRunning) {
    return 'running';
  }
  switch (action.status) {
    case 'done':
      return 'done';
    case 'failed':
      return 'failed';
    case 'skipped':
      return 'pending';
    default:
      return 'number';
  }
}

function targetSegments(preview: AssistantChangePreview, createdId: number | null, refs: Record<string, AssistantMention>): AssistantSegment[] {
  const target = preview.target;
  const id = target.id ?? createdId;
  if (id !== null && refs[`${target.type}:${id}`]) {
    return [entitySegment(target.type, id, refs)];
  }
  return [{kind: 'text', text: target.title}];
}

function changeView(change: {field: string; before: unknown; after: unknown}, refs: Record<string, AssistantMention>): AssistantChangeView {
  return {
    field: change.field,
    before: isEmpty(change.before) ? [] : valueSegments(change.before, refs),
    after: valueSegments(change.after, refs),
  };
}

function isEmpty(value: unknown): boolean {
  return value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0);
}

function valueSegments(value: unknown, refs: Record<string, AssistantMention>, stepRef: StepRef | null = null): AssistantSegment[] {
  if (isEmpty(value)) {
    return [{kind: 'text', text: EMPTY}];
  }
  if (Array.isArray(value)) {
    return value.flatMap((item, index) => index === 0
      ? valueSegments(item, refs, stepRef)
      : [{kind: 'text', text: ', '} as AssistantSegment, ...valueSegments(item, refs, stepRef)]);
  }
  const step = stepRef !== null && typeof value === 'object' && value !== null ? (value as Record<string, unknown>)['$ref'] : undefined;
  if (stepRef !== null && typeof step === 'number') {
    return stepRef(step);
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
