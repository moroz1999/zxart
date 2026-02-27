import {Component, EventEmitter, Input, OnChanges, Output, inject} from '@angular/core';
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
})
export class RatingComponent implements OnChanges {
    @Input() overallRating?: number;
    @Input() userRating?: number;
    @Input() votesAmount?: number;
    @Output() voted: EventEmitter<number> = new EventEmitter();
    public width: number = 0;
    public activeStar?: number = undefined;

    private tooltip = inject(TooltipDirective);

    constructor(
        private iconReg: SvgIconRegistryService,
        private translate: TranslateService,
    ) {
        this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star')?.subscribe();
        this.iconReg.loadSvg(`${environment.svgUrl}x.svg`, 'x')?.subscribe();
    }

    ngOnChanges(): void {
        this.starLeft();
        this.translate.get('zx-vote.votes', {count: this.votesAmount ?? 0}).subscribe(text => {
            this.tooltip.text = text;
        });
    }

    starPointed(star: number) {
        this.activeStar = star;
        this.width = star / 5 * 100;
    }

    starLeft() {
        this.activeStar = undefined;
        this.width = this.overallRating ? (this.overallRating / 5 * 100) : 0;
    }

    starPressed(star: number) {
        this.voted.emit(star);
    }
}
