import './set-public-path';
import {enableProdMode} from '@angular/core';
import {bootstrapApplication} from '@angular/platform-browser';
import 'zone.js';

import {SpaRootComponent} from './app/spa-root.component';
import {appConfig} from './app/app.config';
import {environment} from './environments/environment';

if (environment.production) {
    enableProdMode();
}

bootstrapApplication(SpaRootComponent, appConfig)
    .catch(err => console.error(err));
