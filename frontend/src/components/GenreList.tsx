import { t } from '../i18n/index.ts'

export function GenreList({ genres, className = '' }: { genres: string[]; className?: string }) {
  if (genres.length === 0) return null

  return (
    <ul className={`flex flex-wrap gap-x-1.5 ${className}`} aria-label={t.show.genres}>
      {genres.map((genre, index) => (
        <li key={genre}>
          {index > 0 && (
            <span className="text-dim mr-1.5" aria-hidden="true">
              •
            </span>
          )}
          {genre}
        </li>
      ))}
    </ul>
  )
}
