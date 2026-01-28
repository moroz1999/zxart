import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatChipsModule} from '@angular/material/chips';
import {CommentAuthorDto} from '../../models/comment.dto';

@Component({
  selector: 'app-comment-author',
  standalone: true,
  imports: [CommonModule, MatChipsModule],
  templateUrl: './comment-author.component.html',
  styleUrls: ['./comment-author.component.scss']
})
export class CommentAuthorComponent {
  @Input({required: true}) author!: CommentAuthorDto;
}
