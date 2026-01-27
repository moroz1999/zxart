import {CommonModule} from '@angular/common';
import {Component, EventEmitter, Input, Output} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {ButtonModule} from 'primeng/button';
import {CommentsService} from '../../services/comments.service';
import {CommentDto} from '../../models/comment.dto';

@Component({
  selector: 'app-comment-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule, ButtonModule],
  templateUrl: './comment-form.component.html',
  styleUrls: ['./comment-form.component.scss']
})
export class CommentFormComponent {
  @Input() targetId!: number;
  @Input() commentToEdit?: CommentDto;
  @Output() commentSaved = new EventEmitter<CommentDto>();
  @Output() cancelled = new EventEmitter<void>();

  commentForm: FormGroup;
  isSubmitting = false;
  errorMessage?: string;

  constructor(
    private fb: FormBuilder,
    private commentsService: CommentsService
  ) {
    this.commentForm = this.fb.group({
      content: ['', [Validators.required, Validators.minLength(2)]]
    });
  }

  ngOnInit(): void {
    if (this.commentToEdit) {
      this.commentForm.patchValue({
        content: this.commentToEdit.content
      });
    }
  }

  onSubmit(): void {
    if (this.commentForm.invalid) {
      return;
    }

    this.isSubmitting = true;
    this.errorMessage = undefined;

    const {content} = this.commentForm.value;

    if (this.commentToEdit) {
      this.commentsService.updateComment(this.commentToEdit.id, content).subscribe({
        next: (comment) => {
          this.commentSaved.emit(comment);
          this.isSubmitting = false;
        },
        error: (err) => {
          this.errorMessage = err.message;
          this.isSubmitting = false;
        }
      });
    } else {
      this.commentsService.addComment(this.targetId, content).subscribe({
        next: (comment) => {
          this.commentSaved.emit(comment);
          this.commentForm.reset();
          this.isSubmitting = false;
        },
        error: (err) => {
          this.errorMessage = err.message;
          this.isSubmitting = false;
        }
      });
    }
  }

  onCancel(): void {
    this.cancelled.emit();
  }
}
