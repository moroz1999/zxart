/** Outcome of applying a registration email's verification link. */
export interface VerifyEmailResult {
  success: boolean;
  /** Translation key describing the outcome. */
  message: string;
}
