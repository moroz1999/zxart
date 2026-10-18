import {CommentTargetDto} from '../models/comment.dto';

/** Router link to one comment: the page path plus the fragment of the comment's anchor. */
export interface CommentLink {
  path: string;
  fragment: string;
}

/** Entity pages that keep their comments on an in-page tab, by target type. */
const COMMENTS_TAB_BY_TYPE: Readonly<Record<string, string>> = {
  zxProd: 'discussion',
  author: 'discussion',
  group: 'discussion',
  party: 'activity',
};

/** Matches `/<entity>/<id>` and drops any tab segment that follows it. */
const ENTITY_PATH = /^\/[^/]+\/[^/]+/;

export function commentLink(commentId: number, target: Pick<CommentTargetDto, 'type' | 'url'>): CommentLink {
  const fragment = `comment${commentId}`;
  const tab = COMMENTS_TAB_BY_TYPE[target.type];
  if (tab === undefined) {
    return {path: target.url, fragment};
  }
  const entityPath = ENTITY_PATH.exec(target.url)?.[0] ?? target.url;

  return {path: `${entityPath}/${tab}`, fragment};
}
