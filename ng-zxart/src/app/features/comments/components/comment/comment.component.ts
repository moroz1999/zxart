import {CommonModule} from '@angular/common';
import {Component, Input} from '@angular/core';
import {CommentDto} from '../../models/comment.dto';

@Component({
  selector: 'app-comment',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './comment.component.html',
  styleUrls: ['./comment.component.scss']
})
export class CommentComponent {
  @Input() comment!: CommentDto;
}
