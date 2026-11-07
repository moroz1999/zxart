import {CommonModule} from '@angular/common';
import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {provideRouter, RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentComponent} from './comment.component';

const COMMENT: CommentDto = {
  id: 601327,
  author: null,
  date: '2026-11-07',
  content: 'Comment text',
  originalContent: 'Comment text',
  translated: '',
  canEdit: false,
  canDelete: false,
  target: {
    title: 'Target production',
    url: '/prod/151567',
    type: 'zxProd',
    imageUrl: '/images/prod.png',
  },
  children: [],
};

function renderComment(comment: CommentDto = COMMENT): HTMLElement {
  TestBed.configureTestingModule({
    imports: [CommentComponent, TranslateModule.forRoot()],
    providers: [
      provideRouter([{path: '**', children: []}]),
      {provide: CommentsService, useValue: {deleteComment: vi.fn()}},
    ],
  });
  TestBed.overrideComponent(CommentComponent, {
    set: {
      imports: [CommonModule, RouterLink, TranslateModule],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    },
  });
  const fixture = TestBed.createComponent(CommentComponent);
  fixture.componentRef.setInput('comment', comment);
  fixture.detectChanges();

  return fixture.nativeElement as HTMLElement;
}

describe('CommentComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('links every target representation to the exact comment', () => {
    const element = renderComment();
    const links = Array.from(element.querySelectorAll<HTMLAnchorElement>(
      '.comment-target-image, .comment-target-title',
    ));

    expect(links).toHaveLength(2);
    expect(links.map(link => link.getAttribute('href'))).toEqual([
      '/prod/151567/discussion#comment601327',
      '/prod/151567/discussion#comment601327',
    ]);
  });

  it('links an inline target to the exact comment', () => {
    const element = renderComment({
      ...COMMENT,
      target: {
        title: 'Target production',
        url: '/prod/151567',
        type: 'zxProd',
      },
    });

    expect(element.querySelector<HTMLAnchorElement>('.comment-target-inline a')?.getAttribute('href'))
      .toBe('/prod/151567/discussion#comment601327');
  });

  it('exposes an anchor for the rendered comment', () => {
    const element = renderComment();

    expect(element.querySelector('#comment601327')).not.toBeNull();
  });
});
