import {Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {NgClass, NgIf, NgStyle} from '@angular/common';

@Component({
    selector: 'zx-rating',
    templateUrl: './rating.component.html',
    styleUrls: ['./rating.component.scss'],
    standalone: true,
    imports: [
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        SvgIconComponent,
        NgIf,
        NgStyle,
        NgClass,
    ],
})
export class RatingComponent implements OnChanges {
    @Input() overallRating?: number;
    @Input() userRating?: number;
    @Output() voted: EventEmitter<number> = new EventEmitter();
    public width: number = 0;
    public activeStar?: number = undefined;

    constructor(private iconReg: SvgIconRegistryService) {
        this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star')?.subscribe();
        this.iconReg.loadSvg(`${environment.svgUrl}x.svg`, 'x')?.subscribe();
    }

    ngOnChanges(): void {
        this.starLeft();
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
