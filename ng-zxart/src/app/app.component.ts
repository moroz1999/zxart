import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {LanguageService} from './features/settings/services/language.service';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent implements OnInit {
    constructor(private languageService: LanguageService) {}

    ngOnInit(): void {
        this.languageService.initialize();
    }
}
