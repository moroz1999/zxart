import {Component, HostBinding, Input, OnInit} from '@angular/core';
import {ParserData} from '../models/parser-data';
import {MatDialog} from '@angular/material/dialog';
import {ParsedReleasesComponent} from '../parsed-releases/parsed-releases.component';

const zxFiles = ['scl', 'trd', 'tap', 'tzx']

@Component({
  selector: 'app-parsed-file',
  templateUrl: './parsed-file.component.html',
  styleUrls: ['./parsed-file.component.scss'],
})
export class ParsedFileComponent {
  @Input() public data!: ParserData;
  @Input() public level = 0;
  @HostBinding('class.zx-file') get isZx(): boolean {
    return zxFiles.indexOf(this.data.type) !== -1;
  }

  constructor(
    public dialog: MatDialog,
  ) {
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
