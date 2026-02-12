import {Component, Input, OnInit} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {take} from 'rxjs';
import {CurrentUserService} from './shared/services/current-user.service';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
})
export class AppComponent implements OnInit {
    @Input() language: 'eng' | 'rus' | 'spa' = 'eng';
    private languages = {
        eng: 'en',
        rus: 'ru',
        spa: 'es',
    };

    constructor(
        public translate: TranslateService,
        private currentUserService: CurrentUserService,
    ) {
    }

    ngOnInit(): void {
        if (this.languages[this.language]) {
            const language = this.languages[this.language];
            this.translate.addLangs([language]);
            this.translate.setDefaultLang(language);
        }
        this.currentUserService.loadUser().pipe(take(1)).subscribe();
    }


}
