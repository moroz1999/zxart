import {describe, expect, it} from 'vitest';
import {AssistantMessage, AssistantPlan, AssistantPlanAction, AssistantRun} from '../../../shared/models/assistant-data';
import {AssistantActivityView, AssistantMessageView, AssistantStreamItem} from '../models/assistant-view';
import {buildAssistantView, groupConversations, mentionSegments} from './assistant-view';

const refs = {'prod:5': {title: 'Elite', url: '/prod/5'}};
const run: AssistantRun = {id: 1, type: 'chat', status: 'running', steps: 0, error: null, startedAt: 0, stopRequested: false};

function message(overrides: Partial<AssistantMessage>): AssistantMessage {
  return {id: 1, seq: 1, role: 'assistant', kind: 'text', text: '', payload: null, createdAt: 0, ...overrides};
}

function toolCall(id: number, tool: string, args: Record<string, unknown>, failed = false): AssistantMessage {
  return message({id, seq: id, kind: 'toolCall', text: tool, payload: {tool, arguments: JSON.stringify(args), result: '', failed}});
}

function plan(overrides: Partial<AssistantPlan>): AssistantPlan {
  return {id: 3, title: 'Fix', status: 'proposed', actions: [], createdAt: 0, approvedAt: null, finishedAt: null, ...overrides};
}

function action(overrides: Partial<AssistantPlanAction>): AssistantPlanAction {
  return {tool: 'create_prod', arguments: '{}', kind: 'create', entityType: 'prod', state: null, dependsOn: [], status: 'pending', result: null, error: null, ...overrides};
}

function input(messages: AssistantMessage[], plans: Record<number, AssistantPlan> = {}, activeRun: AssistantRun | null = null) {
  return {conversationId: 1, messages, plans, refs, activeRun, error: null};
}

function messageAt(items: AssistantStreamItem[], index: number): AssistantMessageView {
  const item = items[index];
  if (item.kind !== 'message') {
    throw new Error(`Item ${index} is ${item.kind}`);
  }
  return item.message;
}

function activityAt(items: AssistantStreamItem[], index: number): AssistantActivityView {
  const item = items[index];
  if (item.kind !== 'activity') {
    throw new Error(`Item ${index} is ${item.kind}`);
  }
  return item.activity;
}

function planWith(actions: AssistantPlanAction[], status: AssistantPlan['status']) {
  const view = buildAssistantView(input([message({kind: 'plan', payload: {planId: 3}})], {3: plan({status, actions})}));
  return messageAt(view.items, 0).plan!;
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

describe('buildAssistantView: activity', () => {
  it('folds consecutive tool calls into one activity with a step per call', () => {
    const view = buildAssistantView(input([
      message({id: 1, role: 'user', text: 'import it'}),
      toolCall(2, 'fetch_url', {url: 'https://spectrumcomputing.co.uk/forums/16304'}),
      toolCall(3, 'search', {phrase: 'TurboVektor'}),
      toolCall(4, 'fetch_url', {url: 'https://itch.io/x'}, true),
      message({id: 5, text: 'Not found'}),
    ]));

    expect(view.items.map(item => item.kind)).toEqual(['message', 'activity', 'message']);
    const activity = activityAt(view.items, 1);
    expect(activity.id).toBe(2);
    expect(activity.running).toBe(false);
    expect(activity.failedCount).toBe(1);
    expect(activity.steps.map(step => [step.tool, step.subject, step.failed])).toEqual([
      ['fetch_url', 'https://spectrumcomputing.co.uk/forums/16304', false],
      ['search', 'TurboVektor', false],
      ['fetch_url', 'https://itch.io/x', true],
    ]);
    expect(activity.steps[1].details).toBe(JSON.stringify({tool: 'search', arguments: '{"phrase":"TurboVektor"}', result: '', failed: false}, null, 2));
  });

  it('names an entity a tool reads by its title when it is known, else by its id', () => {
    const view = buildAssistantView(input([toolCall(1, 'get_prod', {id: 5}), toolCall(2, 'get_author', {id: 9}), toolCall(3, 'list_categories', {})]));

    expect(activityAt(view.items, 0).steps.map(step => step.subject)).toEqual(['Elite', '#9', null]);
  });

  it('names a tool it has no phrase for by the default phrase', () => {
    const view = buildAssistantView(input([toolCall(1, 'search', {phrase: 'a'}), toolCall(2, 'brand_new_tool', {})]));

    expect(activityAt(view.items, 0).steps.map(step => step.labelKey)).toEqual(['assistant.tool.search', 'assistant.tool.default']);
  });

  it('marks the last activity as running while a run works', () => {
    const messages = [toolCall(1, 'search', {phrase: 'a'}), message({id: 2, text: 'so'}), toolCall(3, 'search', {phrase: 'b'})];

    const view = buildAssistantView(input(messages, {}, run));

    expect(activityAt(view.items, 0).running).toBe(false);
    expect(activityAt(view.items, 2).running).toBe(true);
  });

  it('leaves the plan progress notes out of the stream: the plan shows them', () => {
    const view = buildAssistantView(input([
      message({id: 1, kind: 'plan', payload: {planId: 3}}),
      message({id: 2, kind: 'planProgress', text: 'Plan 3, action 1: done', payload: {planId: 3, action: 1}}),
      message({id: 3, kind: 'stopped', text: 'Stopped by the user'}),
    ], {3: plan({})}));

    expect(view.items.map(item => item.kind === 'message' ? item.message.kind : item.kind)).toEqual(['plan', 'stopped']);
  });
});

describe('buildAssistantView: messages', () => {
  it('renders the assistant\'s prose with the renderer it is given, and the user\'s as text', () => {
    const view = buildAssistantView(
      input([message({id: 1, text: '**a**'}), message({id: 2, role: 'user', text: '**b**'})]),
      (text, mentions) => `<b>${text}</b>${Object.keys(mentions).join()}`,
    );

    expect(messageAt(view.items, 0).html).toBe('<b>**a**</b>prod:5');
    expect(messageAt(view.items, 1).html).toBeNull();
  });

  it('shows the plan record as the details of a plan message', () => {
    const proposed = plan({});
    const view = buildAssistantView(input([message({kind: 'plan', payload: {planId: 3}})], {3: proposed}));

    expect(messageAt(view.items, 0).details).toBe(JSON.stringify(proposed, null, 2));
  });
});

describe('buildAssistantView: plans', () => {
  it('shows a proposed action by its entity type, kind, target and arguments', () => {
    const planView = planWith([
      action({
        tool: 'update_prod',
        kind: 'update',
        arguments: JSON.stringify({id: 5, year: 1985, groupIds: [5]}),
        state: {id: 5, title: 'Elite', year: 0, groupIds: []},
      }),
      action({tool: 'set_prod_author', kind: 'link', dependsOn: [1]}),
    ], 'proposed');

    expect(planView.canApprove).toBe(true);
    expect(planView.canCancel).toBe(true);
    expect(planView.actions[0]).toEqual({
      number: 1,
      tool: 'update_prod',
      marker: 'number',
      entityType: 'prod',
      changeKind: 'update',
      target: [{kind: 'link', text: 'Elite', url: '/prod/5'}],
      changes: [
        {field: 'year', before: [], after: [{kind: 'text', text: '1985'}]},
        {field: 'groupIds', before: [], after: [{kind: 'link', text: 'Elite #5', url: '/prod/5'}]},
      ],
      consequences: [],
      dependsOn: [],
      status: 'pending',
      waitsFor: null,
      error: null,
    });
    expect(planView.actions[1].dependsOn).toEqual([1]);
  });

  it('names the steps an action takes ids from by what those steps create', () => {
    const planView = planWith([
      action({tool: 'create_author', entityType: 'author', arguments: JSON.stringify({title: 'Iker Lobo'})}),
      action({tool: 'create_prod', arguments: JSON.stringify({title: 'TurboVektor', year: 2026, hardware: ['zx48', 'zx128']})}),
      action({tool: 'set_prod_author', kind: 'link', dependsOn: [1, 2], arguments: JSON.stringify({prodId: {$ref: 2}, authorId: {$ref: 1}, roles: ['code']})}),
    ], 'proposed');

    expect(planView.actions[1].target).toEqual([{kind: 'text', text: 'TurboVektor'}]);
    expect(planView.actions[1].changes).toEqual([
      {field: 'title', before: [], after: [{kind: 'text', text: 'TurboVektor'}]},
      {field: 'year', before: [], after: [{kind: 'text', text: '2026'}]},
      {field: 'hardware', before: [], after: [{kind: 'text', text: 'zx48'}, {kind: 'text', text: ', '}, {kind: 'text', text: 'zx128'}]},
    ]);
    expect(planView.actions[2].changes).toEqual([
      {field: 'prodId', before: [], after: [{kind: 'step', step: 2, text: 'TurboVektor'}]},
      {field: 'authorId', before: [], after: [{kind: 'step', step: 1, text: 'Iker Lobo'}]},
      {field: 'roles', before: [], after: [{kind: 'text', text: 'code'}]},
    ]);
  });

  it('names an entity an argument gives by id with its title and id, whatever type the field is named after', () => {
    const planView = planWith([
      action({tool: 'set_prod_author', arguments: JSON.stringify({prodId: 5, authorId: 9, groupIds: [5], roles: ['code']})}),
    ], 'proposed');

    expect(planView.actions[0].changes.map(change => change.after)).toEqual([
      [{kind: 'link', text: 'Elite #5', url: '/prod/5'}],
      [{kind: 'text', text: '#9'}],
      [{kind: 'link', text: 'Elite #5', url: '/prod/5'}],
      [{kind: 'text', text: 'code'}],
    ]);
  });

  it('shows the images an argument gives as images, without separators between them', () => {
    const planView = planWith([
      action({tool: 'add_prod_files', arguments: JSON.stringify({prodId: 5, kind: 'screenshots', urls: ['https://a.org/1.PNG', 'https://a.org/2.gif?x=1', 'https://a.org/file.tap']})}),
    ], 'proposed');

    expect(planView.actions[0].changes[2].after).toEqual([
      {kind: 'image', url: 'https://a.org/1.PNG'},
      {kind: 'image', url: 'https://a.org/2.gif?x=1'},
      {kind: 'text', text: ', '},
      {kind: 'text', text: 'https://a.org/file.tap'},
    ]);
  });

  it('links a step an action takes an id from once that step created it', () => {
    const planView = planWith([
      action({status: 'done', result: {id: 5}, arguments: JSON.stringify({title: 'Elite'})}),
      action({tool: 'set_prod_author', kind: 'link', status: 'done', arguments: JSON.stringify({prodId: {$ref: 1}})}),
    ], 'done');

    expect(planView.actions[1].changes[0].after).toEqual([{kind: 'link', text: 'Elite', url: '/prod/5'}]);
  });

  it('links what a done action created', () => {
    const planView = planWith([action({status: 'done', result: {id: 5}, arguments: JSON.stringify({title: 'Elite'})})], 'done');

    expect(planView.actions[0].marker).toBe('done');
    expect(planView.actions[0].target).toEqual([{kind: 'link', text: 'Elite', url: '/prod/5'}]);
  });

  it('shows an action of the assistant by its kind and type, with what its target holds before each argument', () => {
    const planView = planWith([action({
      tool: 'update_author',
      kind: 'update',
      entityType: 'author',
      arguments: JSON.stringify({id: 9, realName: 'Jan Kowalski', countryId: 8, wikiLink: 'https://speccy.info/Iker'}),
      state: {id: 9, title: 'Iker Lobo', realName: '', countryId: 7, cityId: 0, wikiLink: '', refs: [], aliases: [], groups: [], counters: {pictures: 0, tunes: 0, prods: 2, comments: 0}},
    })], 'proposed');

    const view = planView.actions[0];
    expect(view.entityType).toBe('author');
    expect(view.changeKind).toBe('update');
    expect(view.target).toEqual([{kind: 'text', text: 'Iker Lobo'}]);
    expect(view.changes).toEqual([
      {field: 'realName', before: [], after: [{kind: 'text', text: 'Jan Kowalski'}]},
      {field: 'countryId', before: [{kind: 'text', text: '#7'}], after: [{kind: 'text', text: '#8'}]},
      {field: 'wikiLink', before: [], after: [{kind: 'text', text: 'https://speccy.info/Iker'}]},
    ]);
    expect(view.consequences).toEqual([]);
    expect(planView.destructiveCount).toBe(0);
  });

  it('leaves out the arguments an action of the assistant gives as null: they are not given', () => {
    const planView = planWith([action({
      tool: 'update_author',
      kind: 'update',
      entityType: 'author',
      arguments: JSON.stringify({id: 9, realName: 'Jan Kowalski', countryId: null, wikiLink: null}),
      state: {id: 9, title: 'Iker Lobo', realName: '', countryId: 7, cityId: 0, wikiLink: '', refs: [], aliases: [], groups: [], counters: {pictures: 0, tunes: 0, prods: 2, comments: 0}},
    })], 'proposed');

    expect(planView.actions[0].changes.map(change => change.field)).toEqual(['realName']);
  });

  it('links the target of an action of the assistant once its title is known', () => {
    const planView = planWith([action({
      tool: 'update_author',
      kind: 'update',
      entityType: 'prod',
      arguments: JSON.stringify({id: 5, title: 'Elite 2'}),
      state: {id: 5, title: 'Elite'},
    })], 'proposed');

    expect(planView.actions[0].target).toEqual([{kind: 'link', text: 'Elite', url: '/prod/5'}]);
  });

  it('counts what a destructive action of the assistant takes with it', () => {
    const planView = planWith([action({
      tool: 'delete_author',
      kind: 'delete',
      entityType: 'author',
      arguments: JSON.stringify({id: 9}),
      state: {id: 9, title: 'Iker Lobo', aliases: [{id: 10, title: 'Iker', startDate: 0, endDate: 0}], groups: [], counters: {pictures: 0, tunes: 3, prods: 2, comments: 0}},
    })], 'proposed');

    expect(planView.actions[0].changes).toEqual([]);
    expect(planView.actions[0].consequences).toEqual(['tunes: 3', 'prods: 2', 'aliases: 1']);
    expect(planView.destructiveCount).toBe(1);
  });

  it('counts the releases a production takes with it', () => {
    const planView = planWith([action({
      tool: 'delete_prod',
      kind: 'delete',
      entityType: 'prod',
      arguments: JSON.stringify({id: 5}),
      state: {id: 5, title: 'Elite', releases: [{id: 6}, {id: 7}], files: [], authors: []},
    })], 'proposed');

    expect(planView.actions[0].consequences).toEqual(['releases: 2']);
  });

  it('shows the files of the list an action of the assistant adds to before the addresses it adds', () => {
    const planView = planWith([action({
      tool: 'add_prod_files',
      kind: 'attachFile',
      entityType: 'prod',
      arguments: JSON.stringify({prodId: 5, kind: 'maps', urls: ['https://a.org/game.tap']}),
      state: {id: 5, title: 'Elite', files: [{kind: 'maps', id: 20, fileName: 'map.png'}, {kind: 'inlays', id: 21, fileName: 'inlay.png'}]},
    })], 'proposed');

    expect(planView.actions[0].changes[2]).toEqual({
      field: 'urls',
      before: [{kind: 'text', text: 'map.png'}],
      after: [{kind: 'text', text: 'https://a.org/game.tap'}],
    });
  });

  it('names the file an action of the assistant deletes by its name', () => {
    const planView = planWith([action({
      tool: 'delete_prod_file',
      kind: 'deleteFile',
      entityType: 'prod',
      arguments: JSON.stringify({prodId: 5, fileId: 21}),
      state: {id: 5, title: 'Elite', files: [{kind: 'maps', id: 20, fileName: 'map.png'}, {kind: 'inlays', id: 21, fileName: 'inlay.png'}]},
    })], 'proposed');

    expect(planView.actions[0].changes[1]).toEqual({field: 'fileId', before: [], after: [{kind: 'text', text: 'inlay.png #21'}]});
  });

  it('shows the file a release has before the address its file is set from', () => {
    const planView = planWith([action({
      tool: 'set_release_file',
      kind: 'attachFile',
      entityType: 'release',
      arguments: JSON.stringify({releaseId: 6, url: 'https://a.org/game.tap'}),
      state: {id: 6, title: 'Tape', fileName: 'intro.tap', files: []},
    })], 'proposed');

    expect(planView.actions[0].changes[1]).toEqual({
      field: 'url',
      before: [{kind: 'text', text: 'intro.tap'}],
      after: [{kind: 'text', text: 'https://a.org/game.tap'}],
    });
  });

  it('names what an action of the assistant creates by the title it gives', () => {
    const planView = planWith([action({
      tool: 'create_author',
      kind: 'create',
      entityType: 'author',
      arguments: JSON.stringify({title: 'Iker Lobo', countryId: 5}),
      state: null,
    })], 'proposed');

    expect(planView.actions[0].target).toEqual([{kind: 'text', text: 'Iker Lobo'}]);
    expect(planView.actions[0].changes[0]).toEqual({field: 'title', before: [], after: [{kind: 'text', text: 'Iker Lobo'}]});
  });

  it('shows the dates an action of the assistant gives as days', () => {
    const start = new Date(1994, 4, 1).getTime() / 1000;
    const planView = planWith([action({
      tool: 'update_author_alias',
      kind: 'update',
      entityType: 'authorAlias',
      arguments: JSON.stringify({id: 10, startDate: start, endDate: 0}),
      state: {id: 10, title: 'Iker', startDate: 0, endDate: 0},
    })], 'proposed');

    expect(planView.actions[0].changes).toEqual([
      {field: 'startDate', before: [], after: [{kind: 'text', text: '1994-05-01'}]},
      {field: 'endDate', before: [], after: [{kind: 'text', text: '—'}]},
    ]);
  });

  it('counts the done actions and marks the one running', () => {
    const planView = planWith([action({status: 'done'}), action({}), action({})], 'executing');

    expect(planView.doneCount).toBe(1);
    expect(planView.actions.map(item => item.marker)).toEqual(['done', 'running', 'number']);
  });

  it('names the step a failed plan stopped at; the skipped steps wait for it', () => {
    const planView = planWith([action({status: 'done'}), action({status: 'failed', error: 'Exists'}), action({status: 'skipped'})], 'failed');

    expect(planView.stoppedAt).toBe(2);
    expect(planView.actions.map(item => item.marker)).toEqual(['done', 'failed', 'pending']);
    expect(planView.actions[2].waitsFor).toBe(2);
  });

  it('offers neither approval nor cancelling while a run is active or once the plan has moved on', () => {
    const messages = [message({kind: 'plan', payload: {planId: 3}})];

    const whileRunning = messageAt(buildAssistantView(input(messages, {3: plan({})}, run)).items, 0).plan!;
    const cancelled = messageAt(buildAssistantView(input(messages, {3: plan({status: 'cancelled'})})).items, 0).plan!;

    expect([whileRunning.canApprove, whileRunning.canCancel]).toEqual([false, false]);
    expect([cancelled.canApprove, cancelled.canCancel]).toEqual([false, false]);
  });
});

describe('groupConversations', () => {
  it('groups the conversations into today, yesterday and earlier, keeping their order', () => {
    const now = new Date(2026, 10, 2, 15, 0);
    const at = (day: number, hour: number) => new Date(2026, 10, day, hour, 0).getTime() / 1000;
    const conversation = (id: number, lastMessageAt: number) => ({id, title: `c${id}`, lastMessageAt, running: false});

    const groups = groupConversations([
      conversation(1, at(2, 12)),
      conversation(2, at(2, 0)),
      conversation(3, at(1, 23)),
      conversation(4, at(20, 10) - 86400 * 30),
    ], now);

    expect(groups.map(group => [group.day, group.conversations.map(item => item.id)])).toEqual([
      ['today', [1, 2]],
      ['yesterday', [3]],
      ['earlier', [4]],
    ]);
  });

  it('leaves out a day without conversations', () => {
    const now = new Date(2026, 10, 2, 15, 0);

    const groups = groupConversations([{id: 1, title: 'a', lastMessageAt: new Date(2026, 9, 1).getTime() / 1000, running: false}], now);

    expect(groups.map(group => group.day)).toEqual(['earlier']);
  });
});
