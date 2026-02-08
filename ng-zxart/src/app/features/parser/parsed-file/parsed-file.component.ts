import {Component, HostBinding, Input} from '@angular/core';
import {ParserData} from '../models/parser-data';
import {MatDialog} from '@angular/material/dialog';
import {ParsedReleasesComponent} from '../parsed-releases/parsed-releases.component';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {NgForOf, NgIf, NgStyle} from '@angular/common';
import {MatButton} from '@angular/material/button';
import {TranslatePipe} from '@ngx-translate/core';
import {ParsedReleaseComponent} from '../parsed-release/parsed-release.component';

const zxFiles = [
    'dsk',
    'tzx',
    'tap',
    'trd',
    'scl',
    'bin',
    'sna',
    'szx',
    'z80',
    'fdi',
    'udi',
    'td0',
    'rom',
    'spg',
];

@Component({
    selector: 'zx-parsed-file',
    templateUrl: './parsed-file.component.html',
    styleUrls: ['./parsed-file.component.scss'],
    standalone: true,
    imports: [
        SvgIconComponent,
        NgIf,
        NgForOf,
        NgStyle,
        MatButton,
        MatButton,
        TranslatePipe,
        TranslatePipe,
        ParsedReleaseComponent,
    ],
})
export class ParsedFileComponent {
    @Input() public data!: ParserData;
    @Input() public level = 0;
    @Input() public insideZx = false;
    @Input() public notFoundOnly = false;

    @HostBinding('class.zx-file') get isZxContainer(): boolean {
        return zxFiles.indexOf(this.data.type) !== -1;
    }

    constructor(
        public dialog: MatDialog,
        private iconReg: SvgIconRegistryService,
    ) {
        this.iconReg.loadSvg(`${environment.svgUrl}disc.svg`, 'disc')?.subscribe();
    }


    public showReleases() {
        let dialogRef = this.dialog.open(ParsedReleasesComponent, {
            width: '500px',
            data: {
                releases: this.data.releases,
            },
        });
    }
}
