import {describe, expect, it} from 'vitest';
import {AssistantMessage, AssistantPlan} from '../../../shared/models/assistant-data';
import {buildAssistantView, mentionSegments} from './assistant-view';

const refs = {'prod:5': {title: 'Elite', url: '/prod/5'}};

function message(overrides: Partial<AssistantMessage>): AssistantMessage {
  return {id: 1, seq: 1, role: 'assistant', kind: 'text', text: '', payload: null, createdAt: 0, ...overrides};
}

function plan(overrides: Partial<AssistantPlan>): AssistantPlan {
  return {id: 3, title: 'Fix', status: 'proposed', actions: [], createdAt: 0, approvedAt: null, finishedAt: null, ...overrides};
}

describe('mentionSegments', () => {
  it('turns a known mention into a link and leaves the rest as text', () => {
    expect(mentionSegments('See [[prod:5]] and [[author:9]].', refs)).toEqual([
      {kind: 'text', text: 'See '},
      {kind: 'link', text: 'Elite', url: '/prod/5'},
      {kind: 'text', text: ' and '},
      {kind: 'text', text: 'author #9'},
      {kind: 'text', text: '.'},
    ]);
  });

  it('answers plain text as one segment', () => {
    expect(mentionSegments('Hello', refs)).toEqual([{kind: 'text', text: 'Hello'}]);
  });
});

describe('buildAssistantView', () => {
  it('shows a tool call by its tool with the payload as details', () => {
    const view = buildAssistantView({
      conversationId: 1,
      messages: [message({kind: 'toolCall', text: 'search', payload: {tool: 'search', failed: true}})],
      plans: {},
      refs: {},
      activeRun: null,
      error: null,
    });

    expect(view.messages[0].toolName).toBe('search');
    expect(view.messages[0].failed).toBe(true);
    expect(view.messages[0].details).toBe(JSON.stringify({tool: 'search', failed: true}, null, 2));
  });

  it('attaches the plan to its message with the preview as rows', () => {
    const view = buildAssistantView({
      conversationId: 1,
      messages: [message({kind: 'plan', text: 'Fix', payload: {planId: 3}})],
      plans: {
        3: plan({
          actions: [
            {
              tool: 'update_prod', arguments: '{}', dependsOn: [], status: 'pending', result: null, error: null,
              preview: {
                kind: 'delete',
                target: {type: 'prod', id: 5, title: 'Elite', createdInStep: null},
                changes: [{field: 'year', before: 0, after: 1985}, {field: 'groups', before: [], after: [{type: 'prod', id: 5}]}],
                consequences: ['Moves it'],
              },
            },
            {tool: 'update_release', arguments: '{}', dependsOn: [1], status: 'pending', result: null, error: null, preview: null},
          ],
        }),
      },
      refs,
      activeRun: null,
      error: null,
    });

    const planView = view.messages[0].plan!;
    expect(planView.canApprove).toBe(true);
    expect(planView.destructiveCount).toBe(1);
    expect(planView.actions[0]).toEqual({
      number: 1,
      tool: 'update_prod',
      changeKind: 'delete',
      target: [{kind: 'link', text: 'Elite', url: '/prod/5'}],
      targetCreatedInStep: null,
      changes: [
        {field: 'year', before: [{kind: 'text', text: '0'}], after: [{kind: 'text', text: '1985'}]},
        {field: 'groups', before: [{kind: 'text', text: '—'}], after: [{kind: 'link', text: 'Elite', url: '/prod/5'}]},
      ],
      consequences: ['Moves it'],
      dependsOn: [],
      status: 'pending',
      error: null,
    });
    expect(planView.actions[1].changeKind).toBeNull();
    expect(planView.actions[1].dependsOn).toEqual([1]);
  });

  it('shows the plan record as the details of a plan message and the payload of the others', () => {
    const proposed = plan({});
    const view = buildAssistantView({
      conversationId: 1,
      messages: [
        message({id: 1, kind: 'plan', payload: {planId: 3}}),
        message({id: 2, kind: 'planProgress', payload: {planId: 3, action: 1}}),
        message({id: 3, kind: 'stopped', text: 'Stopped by the user'}),
      ],
      plans: {3: proposed},
      refs: {},
      activeRun: null,
      error: null,
    });

    expect(view.messages[0].details).toBe(JSON.stringify(proposed, null, 2));
    expect(view.messages[1].details).toBe(JSON.stringify({planId: 3, action: 1}, null, 2));
    expect(view.messages[2].details).toBeNull();
  });

  it('renders the assistant\'s prose with the renderer it is given, and the user\'s as text', () => {
    const view = buildAssistantView({
      conversationId: 1,
      messages: [message({id: 1, text: '**a**'}), message({id: 2, role: 'user', text: '**b**'})],
      plans: {},
      refs,
      activeRun: null,
      error: null,
    }, (text, mentions) => `<b>${text}</b>${Object.keys(mentions).join()}`);

    expect(view.messages[0].html).toBe('<b>**a**</b>prod:5');
    expect(view.messages[1].html).toBeNull();
  });

  it('offers no approval while a run is active or once the plan has moved on', () => {
    const input = {
      conversationId: 1,
      messages: [message({kind: 'plan', payload: {planId: 3}})],
      refs: {},
      error: null,
    };

    expect(buildAssistantView({...input, plans: {3: plan({})}, activeRun: {id: 1, type: 'chat', status: 'running', steps: 0, error: null, startedAt: 0, stopRequested: false}}).messages[0].plan!.canApprove).toBe(false);
    expect(buildAssistantView({...input, plans: {3: plan({status: 'superseded'})}, activeRun: null}).messages[0].plan!.canApprove).toBe(false);
  });
});
