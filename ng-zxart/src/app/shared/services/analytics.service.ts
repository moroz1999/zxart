import {Injectable} from '@angular/core';

type YmFunction = (
  id: number,
  action: string,
  goal: string,
  params?: Record<string, unknown>,
  callback?: () => void
) => void;

@Injectable({
  providedIn: 'root'
})
export class AnalyticsService {
  private readonly metrikaId = 94686067;

  reachGoal(goal: string, params?: Record<string, unknown>, callback?: () => void): void {
    const ym = (window as {ym?: YmFunction}).ym;
    if (!ym) {
      return;
    }
    ym(this.metrikaId, 'reachGoal', goal, params ?? {}, callback ?? (() => undefined));
  }
}
