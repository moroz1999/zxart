import {Component, EventEmitter, inject, Input, OnChanges, Output} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {NgClass, NgIf, NgStyle} from '@angular/common';
import {TranslateService} from '@ngx-translate/core';
import {TooltipDirective} from '../../directives/tooltip/tooltip.directive';
import {VoteService} from '../../services/vote.service';

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
    @Input() elementId?: number;
    @Input() type?: string;
    @Output() voted: EventEmitter<number> = new EventEmitter();

    currentOverallRating?: number;
    currentVotesAmount?: number;
    currentUserRating?: number;

    public width: number = 0;
    public activeStar?: number = undefined;

    private tooltip = inject(TooltipDirective);

    constructor(
        private iconReg: SvgIconRegistryService,
        private translate: TranslateService,
        private voteService: VoteService,
    ) {
        this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star')?.subscribe();
        this.iconReg.loadSvg(`${environment.svgUrl}x.svg`, 'x')?.subscribe();
    }

    ngOnChanges(): void {
        this.currentOverallRating = this.overallRating;
        this.currentVotesAmount = this.votesAmount;
        this.currentUserRating = this.userRating;
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
        this.width = this.currentOverallRating ? (this.currentOverallRating / 5 * 100) : 0;
    }

    starPressed(star: number) {
        const elementId = Number(this.elementId);
        if (elementId && this.type) {
            this.voteService.send(elementId, star, this.type).subscribe(result => {
                this.currentOverallRating = result.votes;
                this.currentVotesAmount = result.votesAmount;
                this.currentUserRating = star || undefined;
                this.starLeft();
            });
        } else {
            this.voted.emit(star);
        }
    }
}
