import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';

@Component({
  selector: 'app-rating',
  templateUrl: './rating.component.html',
  styleUrls: ['./rating.component.scss'],
})
export class RatingComponent implements OnInit {
  @Input() overallRating?: number;
  @Input() userRating?: number;
  @Output() vote: EventEmitter<number> = new EventEmitter();
  public activeStar: number = 0;

  constructor(private iconReg: SvgIconRegistryService) {
    this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star').subscribe();
  }

  ngOnInit(): void {
    if (this.userRating) {
      this.activeStar = this.userRating;
    }
  }

  getWidth(): number {
    return this.overallRating ? (this.overallRating / 5 * 100) : 0;
  }

  starPointed(star: number) {
    this.activeStar = star;
  }

  starLeft() {
    this.activeStar = this.userRating ?? 0;
  }

  starPressed(star: number) {
    this.vote.emit(star);
  }
}
