import {PlaylistDto} from './playlist.model';

export interface PlaylistItemData {
  id: number;
  playlistIds: number[];
}

export interface PlaylistResponseData {
  zxMusic?: PlaylistItemData[];
  zxPicture?: PlaylistItemData[];
  zxProd?: PlaylistItemData[];
  zxRelease?: PlaylistItemData[];
  playlist?: PlaylistDto[];
}
