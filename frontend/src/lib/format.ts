import type { ShowDetail } from '../api/shows.ts'
import { t } from '../i18n/index.ts'

// UTC, so "2011-04-17" is the 17th everywhere and not the 16th west of Greenwich.
const dateFormat = new Intl.DateTimeFormat(t.dateLocale, { dateStyle: 'long', timeZone: 'UTC' })

export function formatDate(isoDate: string | null) {
  return isoDate ? dateFormat.format(new Date(isoDate)) : null
}

export function formatYear(isoDate: string | null) {
  return isoDate ? isoDate.slice(0, 4) : null
}

export function formatYears(premiered: string | null, ended: string | null) {
  return [formatYear(premiered), formatYear(ended)].filter(Boolean).join(' – ') || null
}

export function formatSchedule({ days, time }: ShowDetail['schedule']) {
  const text = days.join(', ')
  if (!text) return null
  return time ? `${text} · ${time}` : text
}

export function formatHostname(url: string | null) {
  if (!url || !URL.canParse(url)) return null

  const { protocol, hostname } = new URL(url)
  if (protocol !== 'http:' && protocol !== 'https:') return null

  return hostname.replace(/^www\./, '')
}
