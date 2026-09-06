import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, Subscription, take} from 'rxjs';
import {
  CitySaveRequest,
  CountrySaveRequest,
  ManagePlacesDto,
} from '../../features/manage-countries/models/manage-place.dto';
import {ManageCountriesApiService} from '../../features/manage-countries/services/manage-countries-api.service';
import {FormLanguage} from '../../shared/models/form-data-response';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormRowComponent} from '../../shared/ui/zx-form/zx-form-row/zx-form-row.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxMultilangFieldComponent} from '../../shared/ui/zx-multilang-field/zx-multilang-field.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

const LIST_URL = '/manage/countries';

/**
 * Create or edit one country or city (`/manage/countries/add`,
 * `/manage/countries/:id`, `/manage/cities/add`, `/manage/cities/:id`).
 *
 * A country and a city hold the same fields — a title per interface language
 * and a pair of coordinates — so one form serves both and the route says which
 * through `data.kind`. A city adds the country it belongs to: the select is
 * where it is placed and where it is moved, it has no usable default, and the
 * form refuses to submit without it, because a city with no country would be
 * unreachable. A creation started from a country row opens with that country
 * selected (`?country=`).
 */
@Component({
  selector: 'zx-manage-place-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormRowComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxMultilangFieldComponent,
    ZxPageLayoutComponent,
    ZxSelectComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
  ],
  templateUrl: './manage-place-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManagePlaceEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    titles: this.fb.nonNullable.control<Record<string, string>>({}),
    // '' is "no country chosen"; the select deals in strings
    countryId: this.fb.nonNullable.control(''),
    latitude: this.fb.nonNullable.control(0),
    longitude: this.fb.nonNullable.control(0),
  });

  creating = false;
  isCity = false;
  loading = true;
  submitting = false;
  errorMessage = '';
  languages: FormLanguage[] = [];
  countryOptions: ZxSelectOption[] = [];

  private elementId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: ManageCountriesApiService,
  ) {}

  get titleKey(): string {
    if (this.creating) {
      return this.isCity ? 'manage-countries.add-city' : 'manage-countries.add';
    }
    return this.isCity ? 'manage-countries.edit-city-title' : 'manage-countries.edit-title';
  }

  ngOnInit(): void {
    this.creating = this.route.snapshot.data['create'] === true;
    this.isCity = this.route.snapshot.data['kind'] === 'city';
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;

    this.subscriptions.add(
      this.api.places$.pipe(take(1)).subscribe(places => {
        this.applyPlaces(places);
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCancel(): void {
    this.router.navigateByUrl(LIST_URL);
  }

  onSubmit(): void {
    if (this.hasBlankTitle()) {
      this.errorMessage = 'manage-countries.error-title-required';
      return;
    }
    if (this.isCity && this.form.getRawValue().countryId === '') {
      this.errorMessage = 'manage-countries.error-country-required';
      return;
    }

    this.submitting = true;
    this.errorMessage = '';

    this.subscriptions.add(
      this.buildSave().subscribe({
        next: () => this.router.navigateByUrl(LIST_URL),
        error: (error: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = error.error?.errorMessage ?? 'manage-countries.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private buildSave(): Observable<ManagePlacesDto> {
    const value = this.form.getRawValue();
    const common = {
      titles: value.titles,
      latitude: Number(value.latitude),
      longitude: Number(value.longitude),
    };

    if (!this.isCity) {
      const request: CountrySaveRequest = this.creating ? common : {...common, id: this.elementId};
      return this.creating ? this.api.createCountry(request) : this.api.updateCountry(request);
    }

    // the country travels with an update too: naming another one moves the city
    const request: CitySaveRequest = {...common, countryId: Number(value.countryId)};
    return this.creating
      ? this.api.createCity(request)
      : this.api.updateCity({...request, id: this.elementId});
  }

  private applyPlaces(places: ManagePlacesDto): void {
    this.languages = places.languages;
    this.countryOptions = places.countries.map(country => ({
      value: String(country.id),
      label: country.title,
    }));

    const place = this.creating
      ? null
      : (this.isCity ? places.cities : places.countries).find(candidate => candidate.id === this.elementId) ?? null;

    if (place === null) {
      const requested = this.route.snapshot.queryParamMap.get('country') ?? '';
      const offered = this.countryOptions.some(option => option.value === requested);
      this.form.patchValue({titles: this.blankTitles(), countryId: offered ? requested : ''});
      return;
    }

    this.form.patchValue({
      titles: {...this.blankTitles(), ...place.titles},
      countryId: 'countryId' in place ? String(place.countryId) : '',
      latitude: place.latitude,
      longitude: place.longitude,
    });
  }

  private blankTitles(): Record<string, string> {
    const titles: Record<string, string> = {};
    for (const language of this.languages) {
      titles[language.id] = '';
    }
    return titles;
  }

  private hasBlankTitle(): boolean {
    const titles = this.form.getRawValue().titles;
    return this.languages.some(language => (titles[language.id] ?? '').trim() === '');
  }
}
