/** Shapes of the `/assistant-data/` endpoint. */

export type AssistantMessageKind = 'text' | 'toolCall' | 'plan' | 'planProgress' | 'error' | 'stopped';
export type AssistantRunStatus = 'running' | 'done' | 'waitingApproval' | 'failed' | 'stopped';
export type AssistantPlanStatus = 'proposed' | 'executing' | 'done' | 'failed' | 'stopped' | 'superseded';
export type AssistantActionStatus = 'pending' | 'done' | 'failed' | 'skipped';

export interface AssistantConversation {
  id: number;
  title: string;
  createdAt: number;
  lastMessageAt: number;
}

export interface AssistantConversationSummary {
  id: number;
  title: string;
  lastMessageAt: number;
  /** A run is working on it. */
  running: boolean;
}

export interface AssistantMessage {
  id: number;
  seq: number;
  role: 'user' | 'assistant';
  kind: AssistantMessageKind;
  text: string;
  payload: Record<string, unknown> | null;
  createdAt: number;
}

export interface AssistantEntityValue {
  type: string;
  id: number;
}

export interface AssistantChangePreview {
  kind: string;
  target: {type: string; id: number | null; title: string; createdInStep: number | null};
  changes: {field: string; before: unknown; after: unknown}[];
  consequences: string[];
}

export interface AssistantPlanAction {
  tool: string;
  arguments: string;
  preview: AssistantChangePreview | null;
  dependsOn: number[];
  status: AssistantActionStatus;
  result: Record<string, unknown> | null;
  error: string | null;
}

export interface AssistantPlan {
  id: number;
  title: string;
  status: AssistantPlanStatus;
  actions: AssistantPlanAction[];
  createdAt: number;
  approvedAt: number | null;
  finishedAt: number | null;
}

export interface AssistantRun {
  id: number;
  type: 'chat' | 'execute';
  status: AssistantRunStatus;
  steps: number;
  error: string | null;
  startedAt: number;
  stopRequested: boolean;
}

export interface AssistantMention {
  title: string;
  url: string;
}

export interface AssistantState {
  conversationId: number;
  messages: AssistantMessage[];
  plans: AssistantPlan[];
  activeRun: AssistantRun | null;
  refs: Record<string, AssistantMention>;
}

export interface AssistantPage {
  url: string;
  entityType?: string;
  entityId?: number;
}

export interface AssistantRunStarted {
  runId: number;
}
