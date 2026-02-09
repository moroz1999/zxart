import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {PlayerMode} from './player-mode';
import {RepeatMode} from './repeat-mode';

export interface PlayerState {
  visible: boolean;
  mode: PlayerMode | null;
  playlistId: string | null;
  playlist: ZxTuneDto[];
  currentIndex: number;
  isPlaying: boolean;
  shuffleEnabled: boolean;
  repeatMode: RepeatMode;
  currentTime: number;
  duration: number;
}
