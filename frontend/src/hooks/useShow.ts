import { useQuery } from '@tanstack/react-query'
import { getShow } from '../api/shows.ts'

export function isValidShowId(id: number) {
  return Number.isInteger(id) && id > 0
}

export function useShow(id: number) {
  return useQuery({
    queryKey: ['shows', 'detail', id],
    queryFn: ({ signal }) => getShow(id, signal),
    enabled: isValidShowId(id),
  })
}
