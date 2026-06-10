import {BreadcrumbItemDto} from '../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';

export type PartyCompoMedium = 'prod' | 'picture' | 'music';

export interface PartyLocationItemDto {
  readonly title: string;
  readonly url: string;
}

export interface PartyLocationDto {
  readonly city: PartyLocationItemDto | null;
  readonly country: PartyLocationItemDto | null;
}

export interface PartyLinkDto {
  readonly url: string;
  readonly label: string;
}

export interface PartyCompoDto {
  readonly compoType: string;
  readonly medium: PartyCompoMedium;
  readonly name: string;
  readonly count: number;
}

export interface PartyEditionDto {
  readonly id: number;
  readonly year: string;
  readonly url: string;
  readonly current: boolean;
}

export interface PartyCountersDto {
  readonly compos: number;
  readonly entries: number;
  readonly authors: number;
  readonly pictures: number;
  readonly tunes: number;
  readonly prods: number;
  readonly comments: number;
}

export interface PartyTabsDto {
  readonly hasOverview: boolean;
  readonly hasCompos: boolean;
  readonly hasActivity: boolean;
}

export interface PartyCoreDto {
  readonly id: number;
  readonly title: string;
  readonly abbreviation: string;
  readonly originalName: string;
  readonly url: string;
  readonly imageUrl: string;
  readonly year: string | null;
  readonly location: PartyLocationDto;
  readonly links: PartyLinkDto[];
  readonly compos: PartyCompoDto[];
  readonly editions: PartyEditionDto[];
  readonly zipUrl: string;
  readonly counters: PartyCountersDto;
  readonly tabs: PartyTabsDto;
  readonly breadcrumbs: BreadcrumbItemDto[];
}
