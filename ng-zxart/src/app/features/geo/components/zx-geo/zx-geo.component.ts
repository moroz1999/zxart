import {CommonModule} from '@angular/common';
import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  HostListener,
  OnDestroy,
  ViewChild,
} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import * as L from 'leaflet';
import {Observable, Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {
  GeoAuthorItem,
  GeoCity,
  GeoCounters,
  GeoCountry,
  GeoEntityType,
  GeoFilter,
  GeoGroupItem,
  GeoListResponse,
  GeoMapResponse,
  GeoPartyItem,
  GeoBounds,
} from '../../models/geo.models';
import {GeoService} from '../../services/geo.service';
import {ThemeService} from '../../../settings/services/theme.service';
import {Theme} from '../../../settings/models/preference.dto';

type GeoListItem = GeoAuthorItem | GeoGroupItem | GeoPartyItem;

interface GeoLayerState {
  authors: boolean;
  groups: boolean;
  parties: boolean;
}

@Component({
  selector: 'zx-geo',
  standalone: true,
  imports: [CommonModule, FormsModule, TranslateModule, ZxButtonComponent, ZxInputComponent, ZxSelectComponent],
  templateUrl: './zx-geo.component.html',
  styleUrl: './zx-geo.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGeoComponent implements AfterViewInit, OnDestroy {
  private static readonly cityZoom = 5;
  private static readonly pageSize = 50;
  private static readonly authorIcon =
    '<svg class="zx-geo-marker__icon" viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-5 6-5s6 1.7 6 5z"/></svg>';
  private static readonly groupIcon =
    '<svg class="zx-geo-marker__icon" viewBox="0 0 16 16" fill="currentColor"><circle cx="5.5" cy="6" r="2.5"/><circle cx="11" cy="6.5" r="2"/><path d="M0.5 14c0-2.6 2.1-4 5-4s5 1.4 5 4z"/><path d="M9.5 14c.2-1.8 1.4-3 3.2-3 1.6 0 2.8 1.1 2.8 3z"/></svg>';
  private static readonly partyIcon =
    '<svg class="zx-geo-marker__icon" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1l1.9 4.1 4.5.5-3.4 3 1 4.4L8 10.8 3.9 13l1-4.4L1.6 5.6l4.5-.5z"/></svg>';

  @ViewChild('mapContainer') private mapContainer?: ElementRef<HTMLDivElement>;

  data: GeoMapResponse = {countries: [], counters: {authors: 0, groups: 0, parties: 0}};
  visibleCountries: GeoCountry[] = [];
  visibleCities: GeoCity[] = [];
  selectedFilter: GeoFilter | null = null;
  layers: GeoLayerState = {authors: true, groups: true, parties: true};
  activeType: GeoEntityType = 'authors';
  list: GeoListResponse<GeoListItem> = {total: 0, items: []};
  listLoading = false;
  page = 1;
  search = '';
  sorting = 'title,asc';
  sortOptions: ZxSelectOption[] = [];

  private readonly subscription = new Subscription();
  private readonly searchTerm$ = new Subject<string>();
  private readonly listRequests$ = new Subject<Observable<GeoListResponse<GeoListItem>>>();
  private map?: L.Map;
  private markerLayer = L.layerGroup();
  private tileLayer?: L.TileLayer;

  constructor(
    private readonly geoService: GeoService,
    private readonly changeDetector: ChangeDetectorRef,
    private readonly translate: TranslateService,
    private readonly themeService: ThemeService,
  ) {}

  ngAfterViewInit(): void {
    this.initMap();
    this.subscription.add(this.themeService.theme$.subscribe(theme => this.applyBasemap(theme)));
    this.subscription.add(this.translate.stream(['geo.sort.title', 'geo.sort.latest']).subscribe(labels => {
      this.sortOptions = [
        {value: 'title,asc', label: labels['geo.sort.title']},
        {value: 'id,desc', label: labels['geo.sort.latest']},
      ];
      this.changeDetector.markForCheck();
    }));
    this.subscription.add(this.translate.onLangChange.subscribe(() => this.changeDetector.markForCheck()));
    this.subscription.add(this.searchTerm$.pipe(
      debounceTime(300),
      distinctUntilChanged(),
    ).subscribe(() => {
      this.page = 1;
      this.loadList();
    }));
    this.subscription.add(this.listRequests$.pipe(
      switchMap(request => request),
    ).subscribe(result => {
      this.list = result;
      this.listLoading = false;
      this.changeDetector.markForCheck();
    }));
    this.subscription.add(this.geoService.map$.subscribe(data => {
      this.data = data;
      this.applyUrlState();
      this.changeDetector.markForCheck();
    }));
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
    this.map?.remove();
  }

  toggleLayer(type: GeoEntityType): void {
    this.layers[type] = !this.layers[type];
    if (!this.layers[this.activeType]) {
      this.activeType = this.enabledEntityTypes[0] ?? 'authors';
    }
    this.page = 1;
    this.refreshMap();
    this.loadList();
  }

  selectCountry(country: GeoCountry, updateHistory = true): void {
    this.selectedFilter = {kind: 'country', country};
    this.page = 1;
    this.search = '';
    this.fitCountry(country);
    this.refreshMap();
    this.refreshPanel();
    if (updateHistory) {
      this.updateUrl();
    }
  }

  selectCity(city: GeoCity, updateHistory = true): void {
    this.selectedFilter = {kind: 'city', city};
    this.page = 1;
    this.search = '';
    this.map?.flyTo([city.latitude, city.longitude], Math.max(this.map.getZoom(), 8));
    this.refreshMap();
    this.refreshPanel();
    if (updateHistory) {
      this.updateUrl();
    }
  }

  selectPlace(place: GeoCountry | GeoCity): void {
    if (this.isCity(place)) {
      this.selectCity(place);
      return;
    }

    this.selectCountry(place);
  }

  clearFilter(updateHistory = true): void {
    this.selectedFilter = null;
    this.page = 1;
    this.refreshMap();
    this.refreshPanel();
    if (updateHistory) {
      this.updateUrl();
    }
  }

  @HostListener('window:popstate')
  onPopState(): void {
    this.applyUrlState();
    this.changeDetector.markForCheck();
  }

  private applyUrlState(): void {
    const params = new URLSearchParams(window.location.search);
    const cityId = Number(params.get('city'));
    const city = cityId ? this.findCity(cityId) : undefined;
    if (city) {
      this.selectCity(city, false);
      return;
    }

    const countryId = Number(params.get('country'));
    const country = countryId ? this.data.countries.find(item => item.id === countryId) : undefined;
    if (country) {
      this.selectCountry(country, false);
      return;
    }

    this.clearFilter(false);
  }

  private updateUrl(): void {
    const params = new URLSearchParams(window.location.search);
    params.delete('country');
    params.delete('city');
    if (this.selectedFilter?.kind === 'country') {
      params.set('country', String(this.selectedFilter.country.id));
    } else if (this.selectedFilter?.kind === 'city') {
      params.set('city', String(this.selectedFilter.city.id));
    }

    const query = params.toString();
    const url = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
    window.history.pushState(null, '', url);
  }

  private findCity(id: number): GeoCity | undefined {
    for (const country of this.data.countries) {
      const city = country.cities.find(item => item.id === id);
      if (city) {
        return city;
      }
    }

    return undefined;
  }

  setType(type: GeoEntityType): void {
    this.activeType = type;
    this.page = 1;
    this.loadList();
  }

  updateSearch(value: string): void {
    this.search = value;
    this.searchTerm$.next(value);
  }

  updateSorting(value: string | string[]): void {
    this.sorting = Array.isArray(value) ? value[0] ?? 'title,asc' : value;
    this.page = 1;
    this.loadList();
  }

  setPage(page: number): void {
    this.page = Math.max(1, Math.min(page, this.pages));
    this.loadList();
  }

  get enabledEntityTypes(): GeoEntityType[] {
    return (['authors', 'groups', 'parties'] as GeoEntityType[]).filter(type => this.layers[type]);
  }

  get scopeCounters(): GeoCounters {
    if (this.selectedFilter?.kind === 'country') {
      return this.selectedFilter.country.counters;
    }
    if (this.selectedFilter?.kind === 'city') {
      return this.selectedFilter.city.counters;
    }

    const places = this.isCountryLevel ? this.visibleCountries : this.visibleCities;

    return places.reduce(
      (total, place) => ({
        authors: total.authors + place.counters.authors,
        groups: total.groups + place.counters.groups,
        parties: total.parties + place.counters.parties,
      }),
      {authors: 0, groups: 0, parties: 0},
    );
  }

  get placesTitle(): string {
    if (this.selectedFilter?.kind === 'country') {
      return 'geo.places.cities';
    }

    return this.isCountryLevel ? 'geo.places.countries' : 'geo.places.cities';
  }

  get showPlaces(): boolean {
    return this.selectedFilter?.kind !== 'city';
  }

  get placeRows(): Array<GeoCountry | GeoCity> {
    if (this.selectedFilter?.kind === 'country') {
      return this.orderPlaces(this.selectedFilter.country.cities);
    }

    if (this.isCountryLevel) {
      return this.orderPlaces(this.visibleCountries);
    }

    return this.orderPlaces(this.visibleCities);
  }

  private orderPlaces<T extends GeoCountry | GeoCity>(places: T[]): T[] {
    return places
      .filter(place => this.total(place.counters) > 0)
      .sort((left, right) => this.total(right.counters) - this.total(left.counters));
  }

  get pages(): number {
    return Math.max(1, Math.ceil(this.list.total / ZxGeoComponent.pageSize));
  }

  get firstShown(): number {
    return this.list.total === 0 ? 0 : (this.page - 1) * ZxGeoComponent.pageSize + 1;
  }

  get lastShown(): number {
    return Math.min(this.page * ZxGeoComponent.pageSize, this.list.total);
  }

  get isCountryLevel(): boolean {
    return (this.map?.getZoom() ?? 2) < ZxGeoComponent.cityZoom;
  }

  get filterTitle(): string {
    if (this.selectedFilter?.kind === 'country') {
      return this.selectedFilter.country.title;
    }
    if (this.selectedFilter?.kind === 'city') {
      return this.selectedFilter.city.title;
    }

    return '';
  }

  isAuthorItem(item: GeoListItem): item is GeoAuthorItem {
    return 'musicRating' in item;
  }

  authorSubtitle(item: GeoAuthorItem): string {
    return [item.realName, this.geoLocation(item)].filter(part => part !== '').join(' · ');
  }

  groupSubtitle(item: GeoGroupItem): string {
    const type = item.groupType && item.groupType !== 'unknown'
      ? this.translate.instant('group-browser.types.' + item.groupType)
      : '';

    return [type, this.geoLocation(item)].filter(part => part !== '').join(' · ');
  }

  partySubtitle(item: GeoPartyItem): string {
    const entries = this.translate.instant('geo.entries', {count: item.entries});

    return [entries, this.geoLocation(item)].filter(part => part !== '').join(' · ');
  }

  private geoLocation(item: {cityTitle: string | null; countryTitle: string | null}): string {
    return [item.cityTitle, item.countryTitle].filter((part): part is string => !!part).join(', ');
  }

  isGroupItem(item: GeoListItem): item is GeoGroupItem {
    return 'groupType' in item;
  }

  isCity(place: GeoCountry | GeoCity): place is GeoCity {
    return 'countryId' in place;
  }

  total(counters: GeoCounters): number {
    return (this.layers.authors ? counters.authors : 0)
      + (this.layers.groups ? counters.groups : 0)
      + (this.layers.parties ? counters.parties : 0);
  }

  private initMap(): void {
    if (!this.mapContainer) {
      return;
    }

    this.map = L.map(this.mapContainer.nativeElement, {minZoom: 2, worldCopyJump: true}).setView([45, 15], 3);
    this.markerLayer.addTo(this.map);
    this.map.on('moveend zoomend', () => {
      this.refreshMap();
      this.refreshPanel();
      this.changeDetector.markForCheck();
    });
  }

  private applyBasemap(theme: Theme): void {
    if (!this.map) {
      return;
    }

    this.tileLayer?.remove();
    const dark = theme === 'dark';
    this.tileLayer = L.tileLayer(
      dark
        ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
        : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      {
        maxZoom: 18,
        attribution: dark ? 'OpenStreetMap, CARTO' : 'OpenStreetMap',
      },
    ).addTo(this.map);
  }

  private refreshMap(): void {
    if (!this.map) {
      return;
    }

    this.markerLayer.clearLayers();
    const places = this.selectedFilter ? this.filterPlaces() : this.mapPlaces();
    for (const place of places) {
      const count = this.total(place.counters);
      if (count === 0) {
        continue;
      }
      const marker = L.marker([place.latitude, place.longitude], {icon: this.createMarkerIcon(place.counters, 'countryId' in place)});
      marker.on('click', () => {
        if ('countryId' in place) {
          this.selectCity(place);
        } else {
          this.selectCountry(place);
        }
      });
      marker.addTo(this.markerLayer);
    }
  }

  private refreshPanel(): void {
    if (!this.map) {
      return;
    }

    if (this.selectedFilter?.kind === 'city') {
      this.visibleCities = [];
      this.visibleCountries = [];
      this.loadList();
      return;
    }

    const bounds = this.map.getBounds();
    this.visibleCities = this.data.countries.flatMap(country => country.cities)
      .filter(city => bounds.contains([city.latitude, city.longitude]));
    this.visibleCountries = this.data.countries.filter(country => bounds.contains([country.latitude, country.longitude]));
    this.loadList();
  }

  private loadList(): void {
    const bounds = this.selectedFilter === null ? this.currentBounds() : null;
    if (this.selectedFilter === null && bounds === null) {
      this.list = {total: 0, items: []};
      this.listLoading = false;
      return;
    }

    const countryId = this.selectedFilter?.kind === 'country' ? this.selectedFilter.country.id : null;
    const cityId = this.selectedFilter?.kind === 'city' ? this.selectedFilter.city.id : null;

    this.listLoading = true;
    this.changeDetector.markForCheck();
    this.listRequests$.next(this.geoService.getList(
      this.activeType,
      (this.page - 1) * ZxGeoComponent.pageSize,
      ZxGeoComponent.pageSize,
      this.sorting,
      countryId,
      cityId,
      bounds,
      this.search,
    ));
  }

  private currentBounds(): GeoBounds | null {
    if (!this.map) {
      return null;
    }

    const bounds = this.map.getBounds();

    return {
      north: bounds.getNorth(),
      south: bounds.getSouth(),
      east: bounds.getEast(),
      west: bounds.getWest(),
    };
  }

  private mapPlaces(): Array<GeoCountry | GeoCity> {
    if (this.isCountryLevel) {
      return this.data.countries;
    }

    return [...this.visibleCities, ...this.cityFreeCountries()];
  }

  private cityFreeCountries(): GeoCountry[] {
    if (!this.map) {
      return [];
    }

    const bounds = this.map.getBounds();

    return this.data.countries
      .filter(country => bounds.contains([country.latitude, country.longitude]))
      .map(country => ({...country, counters: this.cityFreeCounters(country)}))
      .filter(country => this.total(country.counters) > 0);
  }

  private cityFreeCounters(country: GeoCountry): GeoCounters {
    return country.cities.reduce(
      (remaining, city) => ({
        authors: remaining.authors - city.counters.authors,
        groups: remaining.groups - city.counters.groups,
        parties: remaining.parties - city.counters.parties,
      }),
      {...country.counters},
    );
  }

  private filterPlaces(): Array<GeoCountry | GeoCity> {
    if (this.selectedFilter?.kind === 'country') {
      return this.selectedFilter.country.cities;
    }
    if (this.selectedFilter?.kind === 'city') {
      return [this.selectedFilter.city];
    }

    return [];
  }

  private fitCountry(country: GeoCountry): void {
    if (!this.map || country.cities.length === 0) {
      return;
    }

    const bounds = L.latLngBounds(country.cities.map(city => [city.latitude, city.longitude]));
    this.map.flyToBounds(bounds.pad(0.25), {maxZoom: 7});
  }

  private createMarkerIcon(counters: GeoCounters, city: boolean): L.DivIcon {
    const segments = this.markerSegments(counters);
    const height = 24;
    const width = segments.reduce((sum, segment) => sum + 22 + String(segment.count).length * 7, 8);
    const className = city ? 'zx-geo-marker zx-geo-marker--city' : 'zx-geo-marker';
    const inner = segments
      .map(segment => `<span class="zx-geo-marker__seg">${segment.icon}${segment.count}</span>`)
      .join('');

    return L.divIcon({
      className: 'zx-geo-marker-shell',
      html: `<span class="${className}">${inner}</span>`,
      iconSize: [width, height],
      iconAnchor: [width / 2, height / 2],
    });
  }

  private markerSegments(counters: GeoCounters): Array<{count: number; icon: string}> {
    const segments: Array<{count: number; icon: string}> = [];
    if (this.layers.authors && counters.authors > 0) {
      segments.push({count: counters.authors, icon: ZxGeoComponent.authorIcon});
    }
    if (this.layers.groups && counters.groups > 0) {
      segments.push({count: counters.groups, icon: ZxGeoComponent.groupIcon});
    }
    if (this.layers.parties && counters.parties > 0) {
      segments.push({count: counters.parties, icon: ZxGeoComponent.partyIcon});
    }

    return segments;
  }
}
