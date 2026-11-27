import { getJson } from './http.ts'

export interface ShowSummary {
  id: number
  name: string
  image: string | null
  genres: string[]
  rating: number | null
  premiered: string | null
}

export interface CastMember {
  id: number | null
  name: string | null
  character: string | null
  image: string | null
}

export interface ShowDetail extends ShowSummary {
  summary: string | null
  type: string | null
  language: string | null
  status: string | null
  ended: string | null
  runtime: number | null
  network: string | null
  country: string | null
  schedule: { time: string | null; days: string[] }
  officialSite: string | null
  cast: CastMember[]
}

export function searchShows(term: string, signal?: AbortSignal) {
  return getJson<ShowSummary[]>(`/shows?q=${encodeURIComponent(term)}`, signal)
}

export function getShow(id: number, signal?: AbortSignal) {
  return getJson<ShowDetail>(`/shows/${id}`, signal)
}
