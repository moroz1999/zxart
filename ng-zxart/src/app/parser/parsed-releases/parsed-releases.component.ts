import {Component, Inject, OnInit} from '@angular/core';
import {ReleaseData} from '../models/release-data';
import {MAT_DIALOG_DATA} from '@angular/material/dialog';
import {ParsedReleaseComponent} from '../parsed-release/parsed-release.component';
import {NgForOf} from '@angular/common';

interface DialogData {
    releases: ReleaseData[];
}

@Component({
    selector: 'app-parsed-releases',
    templateUrl: './parsed-releases.component.html',
    styleUrls: ['./parsed-releases.component.scss'],
    standalone: true,
    imports: [
        ParsedReleaseComponent,
        NgForOf,
    ],
})
export class ParsedReleasesComponent implements OnInit {
    public releases: ReleaseData[] = [];

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: DialogData,
    ) {
        this.releases = data.releases;
    }

    ngOnInit(): void {
    }

}
