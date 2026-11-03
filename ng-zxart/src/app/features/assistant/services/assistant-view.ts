import {
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
/** The kinds that take the target away, so a plan says what goes with it. */
const REMOVING_KINDS = ['delete', 'join', 'convert'];
/** The argument that names an action's target; the target shows it. */
const TARGET_ARGUMENT = 'id';
const DATE_ARGUMENT = /Date$/;
/** The lists of its state a removing action counts besides its counters. */
const COUNTED_LISTS = ['releases', 'aliases'];
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

/**
 * An action: its arguments as the model gave them, each
 * beside what its target held, and for one that takes the target away, what
 * goes with it.
 */
function actionView(
  action: AssistantPlanAction,
  number: number,
  target: AssistantSegment[],
  refs: Record<string, AssistantMention>,
  stepRef: StepRef,
  isRunning: boolean,
  stoppedAt: number | null,
): AssistantPlanActionView {
  const state = action.state;
  const kind = action.kind;
  return {
    number,
    tool: action.tool,
    marker: actionMarker(action, isRunning),
    entityType: action.entityType,
    changeKind: kind,
    target,
    changes: Object.entries(parseArguments(action.arguments))
      .filter(([field]) => field !== TARGET_ARGUMENT)
      .map(([field, value]) => assistantChange(field, value, kind, action.arguments, state, refs, stepRef)),
    consequences: state !== null && REMOVING_KINDS.includes(kind) ? takenAlong(state) : [],
    dependsOn: action.dependsOn,
    status: action.status,
    waitsFor: action.status === 'skipped' ? stoppedAt : null,
    error: action.error,
  };
}

/**
 * A row of an action: the argument beside what the target holds under its
 * name; the addresses of added files beside the files of their list, the
 * address of a release file beside the file it replaces, a file by its name.
 */
function assistantChange(
  field: string,
  value: unknown,
  kind: string,
  json: string,
  state: Record<string, unknown> | null,
  refs: Record<string, AssistantMention>,
  stepRef: StepRef,
): AssistantChangeView {
  const files = stateFiles(state);
  if (field === 'urls' && kind === 'attachFile') {
    const list = parseArguments(json)['kind'];
    const names = files.filter(file => file.kind === list).map(file => file.fileName);
    return {field, before: names.length > 0 ? assistantSegments(field, names, refs, stepRef) : [], after: assistantSegments(field, value, refs, stepRef)};
  }
  const fileName = state?.['fileName'];
  if (field === 'url' && kind === 'attachFile' && typeof fileName === 'string' && fileName !== '') {
    return {field, before: [{kind: 'text', text: fileName}], after: assistantSegments(field, value, refs, stepRef)};
  }
  const file = field === 'fileId' ? files.find(listed => listed.id === value) : undefined;
  if (file) {
    return {field, before: [], after: [{kind: 'text', text: `${file.fileName} #${file.id}`}]};
  }
  return {
    field,
    before: state !== null && !isUnset(state[field]) ? assistantSegments(field, state[field], refs, stepRef) : [],
    after: assistantSegments(field, value, refs, stepRef),
  };
}

interface StateFile {
  kind: string;
  id: number;
  fileName: string;
}

/** The files a target's state lists. */
function stateFiles(state: Record<string, unknown> | null): StateFile[] {
  const files = state?.['files'];
  return Array.isArray(files)
    ? files.filter((file): file is StateFile => typeof file === 'object' && file !== null && typeof (file as StateFile).id === 'number')
    : [];
}

/** A value of an action: a date as its day, the rest as arguments are shown. */
function assistantSegments(field: string, value: unknown, refs: Record<string, AssistantMention>, stepRef: StepRef): AssistantSegment[] {
  if (DATE_ARGUMENT.test(field) && typeof value === 'number') {
    return [{kind: 'text', text: value === 0 ? EMPTY : dayOf(value)}];
  }
  return argumentSegments(value, ARGUMENT_ID.test(field), refs, stepRef);
}

/** A field the target holds no value in: empty, or 0 for an id or a date. */
function isUnset(value: unknown): boolean {
  return isEmpty(value) || value === 0;
}

/** A Unix timestamp as the day it falls on, here. */
function dayOf(timestamp: number): string {
  const date = new Date(timestamp * 1000);
  const pad = (part: number) => String(part).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

/** What the target's state counts: its works by kind, then its releases and aliases. */
function takenAlong(state: Record<string, unknown>): string[] {
  const counters = state['counters'];
  const counted = typeof counters === 'object' && counters !== null
    ? Object.entries(counters as Record<string, unknown>).filter(([, count]) => typeof count === 'number' && count > 0)
    : [];
  for (const name of COUNTED_LISTS) {
    const list = state[name];
    if (Array.isArray(list) && list.length > 0) {
      counted.push([name, list.length]);
    }
  }
  return counted.map(([name, count]) => `${name}: ${count}`);
}

/** What the action changes: linked once known, else the title its state or arguments give. */
function actionTarget(action: AssistantPlanAction, refs: Record<string, AssistantMention>): AssistantSegment[] {
  const createdId = typeof action.result?.['id'] === 'number' ? action.result['id'] : null;
  const state = action.state;
  const stateId = typeof state?.['id'] === 'number' ? state['id'] : null;
  const id = stateId ?? createdId;
  if (id !== null && refs[`${action.entityType}:${id}`]) {
    return [entitySegment(action.entityType, id, refs)];
  }
  const title = state?.['title'] ?? parseArguments(action.arguments)['title'];
  return typeof title === 'string' && title !== '' ? [{kind: 'text', text: title}] : [];
}

/**
 * An argument named `<name>Id` or `<name>Ids` gives entities by id. The name
 * tells no type (`groupIds` holds group aliases too), so an id is found among
 * the refs by itself: ids are unique across the site.
 */
const ARGUMENT_ID = /^\w+?Ids?$/;
const IMAGE_URL = /^https?:\/\/[^?#]+\.(png|jpe?g|gif|webp)([?#].*)?$/i;

/** An argument's value: an entity named by id with its title and id, an image as an image, the rest as values are. */
function argumentSegments(value: unknown, isId: boolean, refs: Record<string, AssistantMention>, stepRef: StepRef): AssistantSegment[] {
  if (Array.isArray(value) && value.length > 0) {
    const parts = value.map(item => argumentSegments(item, isId, refs, stepRef));
    return parts.flatMap((part, index) => index === 0 || (isImage(part) && isImage(parts[index - 1]))
      ? part
      : [{kind: 'text', text: ', '} as AssistantSegment, ...part]);
  }
  if (isId && typeof value === 'number') {
    const ref = refById(refs, value);
    return [ref ? {kind: 'link', text: `${ref.title} #${value}`, url: ref.url} : {kind: 'text', text: `#${value}`}];
  }
  if (typeof value === 'string' && IMAGE_URL.test(value)) {
    return [{kind: 'image', url: value}];
  }
  return valueSegments(value, stepRef);
}

/** The ref of an id, whatever its type. */
function refById(refs: Record<string, AssistantMention>, id: number): AssistantMention | undefined {
  const suffix = `:${id}`;
  const key = Object.keys(refs).find(candidate => candidate.endsWith(suffix));
  return key !== undefined ? refs[key] : undefined;
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

function isEmpty(value: unknown): boolean {
  return value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0);
}

function valueSegments(value: unknown, stepRef: StepRef): AssistantSegment[] {
  if (isEmpty(value)) {
    return [{kind: 'text', text: EMPTY}];
  }
  if (Array.isArray(value)) {
    return value.flatMap((item, index) => index === 0
      ? valueSegments(item, stepRef)
      : [{kind: 'text', text: ', '} as AssistantSegment, ...valueSegments(item, stepRef)]);
  }
  const step = typeof value === 'object' && value !== null ? (value as Record<string, unknown>)['$ref'] : undefined;
  if (typeof step === 'number') {
    return stepRef(step);
  }
  if (typeof value === 'object') {
    return [{kind: 'text', text: JSON.stringify(value)}];
  }
  return [{kind: 'text', text: String(value)}];
}

function entitySegment(type: string, id: number, refs: Record<string, AssistantMention>): AssistantSegment {
  const ref = refs[`${type}:${id}`];
  return ref ? {kind: 'link', text: ref.title, url: ref.url} : {kind: 'text', text: `${type} #${id}`};
}
