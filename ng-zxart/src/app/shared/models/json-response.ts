export interface JsonResponse<T> {
  responseStatus: ['success', 'fail'];
  responseData: T;
}
