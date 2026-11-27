import { t } from '../i18n/index.ts'

export function Rating({ value }: { value: number | null }) {
  if (value === null) {
    return <span className="text-dim text-sm">{t.show.noRating}</span>
  }

  return (
    <span className="text-score text-sm font-bold tabular-nums" aria-label={t.show.ratingLabel(value.toFixed(1))}>
      ★ {value.toFixed(1)}
    </span>
  )
}
