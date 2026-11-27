import type { ReactNode } from 'react'
import type { ShowDetail } from '../api/shows.ts'
import { t } from '../i18n/index.ts'
import { formatDate, formatSchedule } from '../lib/format.ts'

export function ShowFacts({ show }: { show: ShowDetail }) {
  const network = [show.network, show.country].filter(Boolean).join(' · ') || null

  return (
    <dl className="space-y-2 text-sm break-words">
      <Fact label={t.show.facts.network}>{network}</Fact>
      <Fact label={t.show.facts.schedule}>{formatSchedule(show.schedule)}</Fact>
      <Fact label={t.show.facts.premiered}>{formatDate(show.premiered)}</Fact>
      <Fact label={t.show.facts.ended}>{formatDate(show.ended)}</Fact>
      <Fact label={t.show.facts.language}>{show.language}</Fact>
      <Fact label={t.show.facts.type}>{show.type}</Fact>
    </dl>
  )
}

/** One "Label: value" line; renders nothing when the value is missing. */
function Fact({ label, children }: { label: string; children: ReactNode }) {
  if (!children) return null

  return (
    <div>
      <dt className="text-dim inline">{label}: </dt>
      <dd className="inline">{children}</dd>
    </div>
  )
}
