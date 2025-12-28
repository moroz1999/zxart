import {Component, Input} from '@angular/core';
import {ReleaseData} from '../models/release-data';
import {NgForOf, NgIf} from '@angular/common';

@Component({
    selector: 'app-parsed-release',
    templateUrl: './parsed-release.component.html',
    styleUrls: ['./parsed-release.component.scss'],
    standalone: true,
    imports: [
        NgIf,
        NgForOf,
        NgIf,
        NgIf,
    ],
})
export class ParsedReleaseComponent {
    @Input() release!: ReleaseData;
}
