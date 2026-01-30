import {CommonModule} from '@angular/common';
import {Component, Input, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatDividerModule} from '@angular/material/divider';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentComponent} from '../comment/comment.component';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxHeading3Directive} from '../../../../shared/directives/typography/typography.directives';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {merge, Observable, of, Subject} from 'rxjs';
import {shareReplay, startWith, switchMap} from 'rxjs/operators';

@Component({
  selector: 'app-comments-list',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatButtonModule,
    MatDividerModule,
    CommentComponent,
    CommentFormComponent,
    ZxButtonComponent,
    ZxStackComponent,
    ZxPanelComponent,
    ZxHeading3Directive,
    InViewportDirective
  ],
  templateUrl: './comments-list.component.html',
  styleUrls: ['./comments-list.component.scss']
})
export class CommentsListComponent implements OnInit {
  @Input() elementId?: number;
  @Input() comments: CommentDto[] = [];
  @Input() isRoot: boolean = true;

  showForm = false;

  private becameVisibleSubject = new Subject<void>();
  private reloadSubject = new Subject<void>();
  private hasBecomeVisible = false;

  commentsStream$: Observable<CommentDto[] | null> = of(null);

  constructor(private commentsService: CommentsService) {}

  ngOnInit(): void {
    if (this.isRoot) {
      this.commentsStream$ = merge(this.becameVisibleSubject, this.reloadSubject).pipe(
        switchMap(() => {
          if (this.elementId) {
            return this.commentsService.getComments(this.elementId);
          }
          return of([]);
        }),
        startWith(null),
        shareReplay({bufferSize: 1, refCount: true})
      );
    } else {
      this.commentsStream$ = of(this.comments);
    }
  }

  onInViewport(): void {
    if (!this.isRoot || !this.elementId || this.hasBecomeVisible) {
      return;
    }
    this.hasBecomeVisible = true;
    this.becameVisibleSubject.next();
  }

  onCommentSaved(comment: CommentDto): void {
    this.showForm = false;
    if (this.isRoot && this.hasBecomeVisible) {
      this.reloadSubject.next();
    }
  }
}
