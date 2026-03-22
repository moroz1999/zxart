import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {DIALOG_DATA} from '@angular/cdk/dialog';
import {ReleaseData} from '../models/release-data';
import {ParsedReleaseComponent} from '../parsed-release/parsed-release.component';
import {NgForOf} from '@angular/common';

interface DialogData {
    releases: ReleaseData[];
}

@Component({
    selector: 'zx-parsed-releases',
    templateUrl: './parsed-releases.component.html',
    styleUrls: ['./parsed-releases.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        ParsedReleaseComponent,
        NgForOf,
    ],
})
export class ParsedReleasesComponent {
    public releases: ReleaseData[] = [];

    constructor(
        @Inject(DIALOG_DATA) public data: DialogData,
    ) {
        this.releases = data.releases;
    }
}
