import { QueryClient } from '@tanstack/react-query'
import { ApiError } from './http.ts'

export function createQueryClient() {
  return new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 5 * 60 * 1000,
        refetchOnWindowFocus: false,
        // The backend already retries the upstream call. Here we only give one
        // more chance to failures that are not the client's fault (5xx, network).
        retry: (failureCount, error) =>
          failureCount < 1 && !(error instanceof ApiError && error.status >= 400 && error.status < 500),
      },
    },
  })
}
