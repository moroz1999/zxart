import {AssistantActionStatus, AssistantMessageKind, AssistantPlanStatus} from '../../../shared/models/assistant-data';

/** A run of text, or a link to an entity the text mentions. */
export type AssistantSegment =
  | {kind: 'text'; text: string}
  | {kind: 'link'; text: string; url: string};

export interface AssistantChangeView {
  field: string;
  before: AssistantSegment[];
  after: AssistantSegment[];
}

export interface AssistantPlanActionView {
  number: number;
  tool: string;
  /** The change kind of the preview; null when the action has none. */
  changeKind: string | null;
  target: AssistantSegment[];
  /** The plan step that creates the target, when it does not exist yet. */
  targetCreatedInStep: number | null;
  changes: AssistantChangeView[];
  consequences: string[];
  dependsOn: number[];
  status: AssistantActionStatus;
  error: string | null;
}

export interface AssistantPlanView {
  id: number;
  title: string;
  status: AssistantPlanStatus;
  canApprove: boolean;
  destructiveCount: number;
  actions: AssistantPlanActionView[];
}

export interface AssistantMessageView {
  id: number;
  role: 'user' | 'assistant';
  kind: AssistantMessageKind;
  segments: AssistantSegment[];
  /** The assistant's prose as HTML, once Markdown is loaded; null otherwise. */
  html: string | null;
  /** The tool a tool call called. */
  toolName: string | null;
  /** The message's payload as pretty JSON, for the details toggle. */
  details: string | null;
  failed: boolean;
  plan: AssistantPlanView | null;
}

export interface AssistantView {
  conversationId: number | null;
  hasConversation: boolean;
  messages: AssistantMessageView[];
  running: boolean;
  error: string | null;
}
