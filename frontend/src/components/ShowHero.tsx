import type { ReactNode } from 'react'
import type { ShowDetail } from '../api/shows.ts'
import { t } from '../i18n/index.ts'
import { GUTTER } from '../layout.ts'
import { formatHostname, formatYears } from '../lib/format.ts'
import { GenreList } from './GenreList.tsx'
import { Poster } from './Poster.tsx'
import { Rating } from './Rating.tsx'

/** Poster column: a thumbnail above the title on phones, full size beside it from md up. */
const POSTER_WIDTH = 'w-36 min-[400px]:w-44 md:w-[260px]'

interface Props {
  show: ShowDetail
  /** Shown above the poster, e.g. the link back to the search. */
  children?: ReactNode
}

/** Top of the detail page: poster, title and key facts over a blurred backdrop. */
export function ShowHero({ show, children }: Props) {
  const years = formatYears(show.premiered, show.ended)

  return (
    <section className="relative isolate overflow-hidden">
      {show.image && (
        <img
          src={show.image}
          alt=""
          aria-hidden="true"
          className="absolute inset-0 -z-20 h-full w-full scale-110 object-cover object-top opacity-40 blur-2xl"
        />
      )}
      <div className="to-base absolute inset-0 -z-10 bg-gradient-to-b from-black/60 via-black/40" />

      <div className={`pt-6 pb-8 sm:pt-10 sm:pb-12 ${GUTTER}`}>
        {children}

        <article className="mt-6 flex flex-col gap-6 md:mt-8 md:flex-row md:items-end md:gap-8">
          <Poster
            src={show.image}
            name={show.name}
            className={`shrink-0 rounded-md shadow-2xl shadow-black ${POSTER_WIDTH}`}
          />

          <div className="min-w-0 md:pb-2">
            <h1 className="text-3xl font-black tracking-tight text-balance break-words min-[400px]:text-4xl sm:text-5xl lg:text-6xl">
              {show.name}
            </h1>

            <div className="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2">
              <Rating value={show.rating} />
              {years && <span className="text-muted text-sm">{years}</span>}
              {show.status && (
                <span className="border-dim text-muted rounded-sm border px-1.5 text-xs leading-5">{show.status}</span>
              )}
              {show.runtime && <span className="text-muted text-sm">{t.show.runtime(show.runtime)}</span>}
            </div>

            <GenreList genres={show.genres} className="mt-3 text-sm" />

            <OfficialSiteLink url={show.officialSite} />
          </div>
        </article>
      </div>
    </section>
  )
}

function OfficialSiteLink({ url }: { url: string | null }) {
  const hostname = formatHostname(url)
  if (!url || !hostname) return null

  return (
    <a
      href={url}
      target="_blank"
      rel="noreferrer"
      aria-label={hostname}
      className="mt-6 flex w-full items-center justify-center gap-2 rounded bg-white px-6 py-2.5 font-bold text-black transition-colors hover:bg-white/80 sm:inline-flex sm:w-auto"
    >
      <span aria-hidden="true">▶</span> {t.show.officialSite}
    </a>
  )
}

export function ShowHeroSkeleton() {
  return (
    <div
      role="status"
      aria-label={t.show.loading}
      className="mt-6 flex animate-pulse flex-col gap-6 md:mt-8 md:flex-row md:gap-8"
    >
      <div className={`bg-surface aspect-[210/295] shrink-0 rounded-md ${POSTER_WIDTH}`} />
      <div className="flex-1 space-y-4">
        <div className="bg-surface h-9 w-2/3 rounded sm:h-12" />
        <div className="bg-surface h-4 w-1/3 rounded" />
        <div className="bg-surface h-28 max-w-2xl rounded" />
      </div>
    </div>
  )
}
