import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ReleaseData} from '../models/release-data';
import {NgForOf, NgIf} from '@angular/common';


import {RouterLink} from '@angular/router';@Component({
    selector: 'zx-parsed-release',
    templateUrl: './parsed-release.component.html',
    styleUrls: ['./parsed-release.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [RouterLink, 
        NgIf,
        NgForOf,
        NgIf,
        NgIf,
    ],
})
export class ParsedReleaseComponent {
    @Input() release!: ReleaseData;
}
