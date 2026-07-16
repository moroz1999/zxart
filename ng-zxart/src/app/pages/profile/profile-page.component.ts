import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {UserProfileDto} from '../../features/profile/models/user-profile.dto';
import {ProfileApiService} from '../../features/profile/services/profile-api.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';

interface ProfileRow {
  labelKey: string;
  value: string;
}

/** Routed page for `profile` — read view of the current user's profile. */
@Component({
  selector: 'zx-profile-page',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslateModule, ZxButtonComponent],
  templateUrl: './profile-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProfilePageComponent {
  readonly profile$: Observable<UserProfileDto | null> = this.api.getProfile();

  constructor(private readonly api: ProfileApiService) {}

  rows(profile: UserProfileDto): ProfileRow[] {
    return [
      {labelKey: 'profile.first-name', value: profile.firstName},
      {labelKey: 'profile.last-name', value: profile.lastName},
      {labelKey: 'profile.company', value: profile.company},
      {labelKey: 'profile.email', value: profile.email},
      {labelKey: 'profile.phone', value: profile.phone},
      {labelKey: 'profile.website', value: profile.website},
      {labelKey: 'profile.address', value: profile.address},
      {labelKey: 'profile.city', value: profile.city},
      {labelKey: 'profile.post-index', value: profile.postIndex},
      {labelKey: 'profile.country', value: profile.country},
    ].filter(row => row.value !== '');
  }
}
