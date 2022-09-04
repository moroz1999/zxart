import {Component, Input, OnInit} from '@angular/core';
import {ReleaseData} from '../models/release-data';

@Component({
  selector: 'app-parsed-release',
  templateUrl: './parsed-release.component.html',
  styleUrls: ['./parsed-release.component.scss'],
})
export class ParsedReleaseComponent {
  @Input() release!: ReleaseData;
}
