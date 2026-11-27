import type { ShowSummary } from '../api/shows.ts'
import { ShowCard, ShowCardSkeleton } from './ShowCard.tsx'

const GRID = 'grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 sm:gap-y-8 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6'

interface Props {
  shows: ShowSummary[]
  /** Fades the cards while they are about to be replaced by a newer search. */
  dimmed?: boolean
}

export function ShowGrid({ shows, dimmed = false }: Props) {
  return (
    <ul className={`${GRID} transition-opacity ${dimmed ? 'opacity-50' : ''}`}>
      {shows.map((show) => (
        <ShowCard key={show.id} show={show} />
      ))}
    </ul>
  )
}

export function ShowGridSkeleton({ cards = 10 }: { cards?: number }) {
  return (
    <ul className={GRID}>
      {Array.from({ length: cards }, (_, index) => (
        <ShowCardSkeleton key={index} />
      ))}
    </ul>
  )
}
