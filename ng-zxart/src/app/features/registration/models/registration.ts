export interface RegistrationPayload {
  userName: string;
  email: string;
  password: string;
  passwordRepeat: string;
  firstName?: string;
  lastName?: string;
  company?: string;
  address?: string;
  city?: string;
  postIndex?: string;
  country?: string;
  phone?: string;
  website?: string;
}

export interface RegistrationResult {
  success: boolean;
  message: string;
}
