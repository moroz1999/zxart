export interface CurrentUser {
  id: number | null;
  userName: string;
  registrationUrl: string | null;
  passwordReminderUrl: string | null;
  profileUrl: string | null;
  playlistsUrl: string | null;
  authorPageUrl: string | null;
}
