import {InjectionToken} from '@angular/core';

/**
 * Opens a fullscreen gallery registered under an id at one of its images. The
 * gallery itself is a feature; entities and shared UI reach it through this
 * token, which the application binds to the feature's service.
 */
export interface GalleryOpener {
  open(galleryId: string, index: number): Promise<void>;
}

export const GALLERY_OPENER = new InjectionToken<GalleryOpener>('GALLERY_OPENER');
