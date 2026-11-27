import { t } from '../i18n/index.ts'

interface Props {
  src: string | null
  name: string
  className?: string
}

export function Poster({ src, name, className = '' }: Props) {
  return (
    <div className={`bg-surface aspect-[210/295] overflow-hidden ${className}`}>
      {src ? (
        <img src={src} alt="" loading="lazy" className="h-full w-full object-cover" />
      ) : (
        <div className="from-raised to-surface text-muted flex h-full items-center justify-center bg-gradient-to-br p-4 text-center font-bold">
          {name}
          <span className="sr-only"> ({t.show.noImage})</span>
        </div>
      )}
    </div>
  )
}
