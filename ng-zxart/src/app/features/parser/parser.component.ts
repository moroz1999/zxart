import {ChangeDetectorRef, Component} from '@angular/core';
import {ParserService} from '../../shared/services/parser.service';
import {ParserData} from './models/parser-data';
import {FormControl, ReactiveFormsModule} from '@angular/forms';
import {TranslatePipe} from '@ngx-translate/core';
import {NgForOf, NgIf} from '@angular/common';
import {MatButton} from '@angular/material/button';
import {ParsedFileComponent} from './parsed-file/parsed-file.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';

@Component({
    selector: 'zx-parser',
    templateUrl: './parser.component.html',
    styleUrls: ['./parser.component.scss'],
    standalone: true,
    imports: [
        TranslatePipe,
        ReactiveFormsModule,
        NgForOf,
        NgIf,
        MatButton,
        ZxSpinnerComponent,
        ParsedFileComponent,
    ],
})
export class ParserComponent {
    private file?: File;
    public error?: string;
    public data?: ParserData[];
    public loading = false;
    public notFoundOnly: FormControl;

    constructor(
        private parser: ParserService,
        private cdr: ChangeDetectorRef,
    ) {
        this.notFoundOnly = new FormControl();
    }

    public fileChanged(event: Event) {
        this.loading = false;
        this.data = undefined;
        const target = event.target as HTMLInputElement;
        this.file = (target.files as FileList)[0];
        if (this.file.size > 1024 * 1024 * 50) {
            this.error = 'File is too big';
        } else {
            this.error = undefined;
        }
    }

    public load() {
        if (this.file) {
            this.loading = true;
            this.parser.parseData(this.file).subscribe(
                response => {
                    this.data = response;
                    this.loading = false;
                },
                error => {
                    this.error = error.message;
                    this.loading = false;
                },
            );
        }
    }
}
