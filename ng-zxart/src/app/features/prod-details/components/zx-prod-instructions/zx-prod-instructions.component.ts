import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ProdDescriptionApiService} from '../../services/prod-description-api.service';
import {ProdDescriptionDto} from '../../models/prod-description.dto';

@Component({
  selector: 'zx-prod-instructions',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective, TextDirective, ZxStackComponent],
  templateUrl: './zx-prod-instructions.component.html',
  styleUrls: ['./zx-prod-instructions.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInstructionsComponent implements OnInit {
  @Input({required: true}) elementId!: number;

  data$: Observable<ProdDescriptionDto | null> = of(null);

  constructor(private readonly api: ProdDescriptionApiService) {}

  ngOnInit(): void {
    this.data$ = this.api.getDescription(this.elementId);
  }
}
