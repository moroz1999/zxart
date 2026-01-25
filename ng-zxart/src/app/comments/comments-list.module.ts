import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {CommentsListComponent} from './comments-list.component';
import {CommentComponent} from './comment.component';

@NgModule({
  declarations: [
    CommentsListComponent,
    CommentComponent
  ],
  imports: [
    CommonModule
  ],
  exports: [
    CommentsListComponent
  ]
})
export class CommentsListModule { }
