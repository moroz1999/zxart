import {
  AssistantActionStatus,
  AssistantConversationSummary,
  AssistantMessageKind,
  AssistantPlanStatus,
} from '../../../shared/models/assistant-data';
import {ZxStepStatus} from '../../../shared/ui/zx-steps/zx-step.component';

/**
 * A run of text, a link to an entity the text mentions, what an earlier plan
 * step makes, before it exists, or an image an address points to.
 */
export type AssistantSegment =
  | {kind: 'text'; text: string}
  | {kind: 'link'; text: string; url: string}
  | {kind: 'step'; step: number; text: string}
  | {kind: 'image'; url: string};

export interface AssistantChangeView {
  field: string;
  /** Empty when the field had no value. */
  before: AssistantSegment[];
  after: AssistantSegment[];
}

export interface AssistantPlanActionView {
  number: number;
  tool: string;
  marker: ZxStepStatus;
  /** The type of the entity the action changes; null when it has no preview. */
  entityType: string | null;
  /** The change kind of the preview; null when the action has none. */
  changeKind: string | null;
  /** The target, a link once it exists; for an action without a preview, the title its fields give. */
  target: AssistantSegment[];
  /** The plan step that creates the target, when it does not exist yet. */
  targetCreatedInStep: number | null;
  /** The preview's rows; for an action without a preview, its arguments. */
  changes: AssistantChangeView[];
  consequences: string[];
  dependsOn: number[];
  status: AssistantActionStatus;
  /** The failed step a skipped one waited for. */
  waitsFor: number | null;
  error: string | null;
}

export interface AssistantPlanView {
  id: number;
  title: string;
  status: AssistantPlanStatus;
  canApprove: boolean;
  canCancel: boolean;
  destructiveCount: number;
  doneCount: number;
  /** The step that failed; null unless one did. */
  stoppedAt: number | null;
  actions: AssistantPlanActionView[];
}

export interface AssistantMessageView {
  id: number;
  role: 'user' | 'assistant';
  kind: AssistantMessageKind;
  segments: AssistantSegment[];
  /** The assistant's prose as HTML, once Markdown is loaded; null otherwise. */
  html: string | null;
  /** The message's payload as pretty JSON, for the details toggle. */
  details: string | null;
  plan: AssistantPlanView | null;
}

/** One tool call of an activity. */
export interface AssistantActivityStepView {
  id: number;
  tool: string;
  /** The translation key of the step's phrase; it takes `subject`. */
  labelKey: string;
  /** What the call was about: a query, an address, an entity; null when nothing. */
  subject: string | null;
  failed: boolean;
  details: string;
}

/** The tool calls the assistant made in a row. */
export interface AssistantActivityView {
  /** The id of its first message. */
  id: number;
  steps: AssistantActivityStepView[];
  failedCount: number;
  /** The run is still adding to it. */
  running: boolean;
}

export type AssistantStreamItem =
  | {kind: 'message'; message: AssistantMessageView}
  | {kind: 'activity'; activity: AssistantActivityView};

export interface AssistantView {
  conversationId: number | null;
  hasConversation: boolean;
  items: AssistantStreamItem[];
  running: boolean;
  error: string | null;
}

export interface AssistantConversationGroup {
  day: 'today' | 'yesterday' | 'earlier';
  conversations: AssistantConversationSummary[];
}
