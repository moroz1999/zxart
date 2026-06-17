export interface FeedbackRequest {
  name: string;
  email: string;
  message: string;
}

export interface FeedbackResponse {
  success: boolean;
}
