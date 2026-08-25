export interface RegistrationPayload {
  userName: string;
  email: string;
  password: string;
  passwordRepeat: string;
}

export interface RegistrationResult {
  success: boolean;
  message: string;
}
