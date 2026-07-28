export interface UserPlaylist {
  id: number;
  title: string;
  pictures: number;
  tunes: number;
  prods: number;
}

export interface PlaylistsResponse {
  playlists: UserPlaylist[];
}
