import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { searchShows } from '../api/shows.ts'

export function useShowSearch(term: string) {
  const query = term.trim()

  return useQuery({
    queryKey: ['shows', 'search', query.toLowerCase()],
    queryFn: ({ signal }) => searchShows(query, signal),
    enabled: query.length > 0,
    // Keep the previous cards on screen while the next search loads.
    placeholderData: keepPreviousData,
  })
}
