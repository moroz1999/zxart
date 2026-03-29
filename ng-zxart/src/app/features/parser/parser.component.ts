import {ChangeDetectionStrategy, Component, OnDestroy} from '@angular/core';
import {ParserService} from '../../shared/services/parser.service';
import {ParserData} from './models/parser-data';
import {FormControl, ReactiveFormsModule} from '@angular/forms';
import {TranslatePipe} from '@ngx-translate/core';
import {AsyncPipe, NgForOf, NgIf} from '@angular/common';
import {ParsedFileComponent} from './parsed-file/parsed-file.component';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {BehaviorSubject, Subscription} from 'rxjs';

interface ParserVm {
    loading: boolean;
    data?: ParserData[];
    error?: string;
}

@Component({
    selector: 'zx-parser',
    templateUrl: './parser.component.html',
    styleUrls: ['./parser.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        ReactiveFormsModule,
        NgForOf,
        NgIf,
        AsyncPipe,
        ZxButtonComponent,
        ZxSpinnerComponent,
        ParsedFileComponent,
    ],
})
export class ParserComponent implements OnDestroy {
    private file?: File;
    private readonly subscription = new Subscription();
    private readonly state = new BehaviorSubject<ParserVm>({loading: false});

    readonly vm$ = this.state.asObservable();
    readonly notFoundOnly = new FormControl();

    constructor(private readonly parser: ParserService) {}

    fileChanged(event: Event): void {
        const target = event.target as HTMLInputElement;
        const file = (target.files as FileList)[0];
        this.file = file;
        this.state.next({
            loading: false,
            error: file.size > 1024 * 1024 * 50 ? 'File is too big' : undefined,
        });
    }

    load(): void {
        if (!this.file) {
            return;
        }
        this.state.next({loading: true});
        this.subscription.add(
            this.parser.parseData(this.file).subscribe({
                next: data => this.state.next({loading: false, data}),
                error: (err: Error) => this.state.next({loading: false, error: err.message}),
            }),
        );
    }

    ngOnDestroy(): void {
        this.subscription.unsubscribe();
    }
}
