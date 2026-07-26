/** The current user's own account (`/profile-data/`). Both fields are read-only. */
export interface UserProfileDto {
  userName: string;
  email: string;
}

/** Password change payload for `/profile-data/?action=change-password`. */
export interface PasswordChangeRequest {
  currentPassword: string;
  password: string;
  passwordRepeat: string;
}
