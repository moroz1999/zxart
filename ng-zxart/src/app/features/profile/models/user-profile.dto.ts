/** Editable self-service profile fields for the current user (`/profile-data/`). */
export interface UserProfileDto {
  userName: string;
  company: string;
  firstName: string;
  lastName: string;
  address: string;
  city: string;
  postIndex: string;
  country: string;
  email: string;
  phone: string;
  website: string;
  subscribe: boolean;
  showemail: boolean;
}
