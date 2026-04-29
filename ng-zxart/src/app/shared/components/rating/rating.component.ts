import {ChangeDetectionStrategy, Component, EventEmitter, inject, Input, OnChanges, Output} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {NgClass, NgIf, NgStyle} from '@angular/common';
import {TranslateService} from '@ngx-translate/core';
import {TooltipDirective} from '../../directives/tooltip/tooltip.directive';

@Component({
    selector: 'zx-rating',
    templateUrl: './rating.component.html',
    styleUrls: ['./rating.component.scss'],
    standalone: true,
    imports: [
        SvgIconComponent,
        NgIf,
        NgStyle,
        NgClass,
    ],
    hostDirectives: [TooltipDirective],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RatingComponent implements OnChanges {
    @Input() overallRating?: number;
    @Input() userRating?: number;
    @Input() votesAmount?: number;
    @Output() voted: EventEmitter<number> = new EventEmitter();

    activeStar?: number = undefined;

    private tooltip = inject(TooltipDirective);

    constructor(
        private iconReg: SvgIconRegistryService,
        private translate: TranslateService,
    ) {
        this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star')?.subscribe();
        this.iconReg.loadSvg(`${environment.svgUrl}x.svg`, 'x')?.subscribe();
    }

    ngOnChanges(): void {
        this.translate.get('zx-vote.votes', {count: this.votesAmount ?? 0}).subscribe(text => {
            this.tooltip.text = text;
        });
    }

    get width(): number {
        if (this.activeStar !== undefined) {
            return this.activeStar * 20;
        }
        return this.overallRating ? this.overallRating * 20 : 0;
    }

    starPointed(star: number): void {
        this.activeStar = star;
    }

    starLeft(): void {
        this.activeStar = undefined;
    }

    starPressed(star: number): void {
        this.voted.emit(star);
    }
}
