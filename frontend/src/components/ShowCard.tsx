import { Link } from 'react-router'
import type { ShowSummary } from '../api/shows.ts'
import { useCameFromState } from '../hooks/useBackToSearch.ts'
import { formatYear } from '../lib/format.ts'
import { GenreList } from './GenreList.tsx'
import { Poster } from './Poster.tsx'
import { Rating } from './Rating.tsx'

export function ShowCard({ show }: { show: ShowSummary }) {
  const cameFrom = useCameFromState()

  return (
    <li className="relative hover:z-10 focus-within:z-10">
      <Link
        to={`/shows/${show.id}`}
        // Lets the detail page send the user back to this exact search.
        state={cameFrom}
        className="bg-surface flex h-full flex-col overflow-hidden rounded-md shadow-black/80 transition duration-300 hover:scale-105 hover:shadow-2xl focus-visible:scale-105 motion-reduce:transition-none motion-reduce:hover:scale-100"
      >
        <Poster src={show.image} name={show.name} />
        <div className="flex min-w-0 flex-1 flex-col gap-1.5 p-2.5 sm:p-3">
          <h2 className="leading-snug font-bold break-words max-sm:text-sm">{show.name}</h2>
          <div className="flex flex-wrap items-baseline gap-x-2">
            <Rating value={show.rating} />
            {show.premiered && <span className="text-muted text-sm">{formatYear(show.premiered)}</span>}
          </div>
          <GenreList genres={show.genres} className="text-muted text-xs" />
        </div>
      </Link>
    </li>
  )
}

export function ShowCardSkeleton() {
  return (
    <li className="bg-surface animate-pulse overflow-hidden rounded-md" aria-hidden="true">
      <div className="bg-raised aspect-[210/295]" />
      <div className="space-y-2 p-2.5 sm:p-3">
        <div className="bg-raised h-4 w-3/4 rounded" />
        <div className="bg-raised h-3 w-1/2 rounded" />
      </div>
    </li>
  )
}
