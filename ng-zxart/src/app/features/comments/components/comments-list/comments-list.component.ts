import {CommonModule} from '@angular/common';
import {Component, Input, OnInit} from '@angular/core';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentComponent} from '../comment/comment.component';

@Component({
  selector: 'app-comments-list',
  standalone: true,
  imports: [CommonModule, CommentComponent],
  templateUrl: './comments-list.component.html',
  styleUrls: ['./comments-list.component.scss']
})
export class CommentsListComponent implements OnInit {
  @Input() elementId?: number;
  @Input() comments: CommentDto[] = [];
  @Input() isRoot: boolean = true;

  constructor(private commentsService: CommentsService) {}

  ngOnInit(): void {
    if (this.isRoot && this.elementId) {
      this.commentsService.getComments(this.elementId).subscribe(data => {
        this.comments = data;
      });
    }
  }
}
