import type { CastMember } from '../api/shows.ts'
import { t } from '../i18n/index.ts'
import { Poster } from './Poster.tsx'

export function CastList({ cast, className = '' }: { cast: CastMember[]; className?: string }) {
  if (cast.length === 0) return null

  return (
    <section className={className}>
      <h2 className="text-xl font-bold sm:text-2xl">{t.show.cast}</h2>
      <ul className="mt-4 grid grid-cols-2 gap-x-3 gap-y-6 min-[400px]:grid-cols-3 sm:grid-cols-4 md:grid-cols-6 xl:grid-cols-8">
        {cast.map((member, index) => (
          <li key={`${member.id}-${index}`} className="min-w-0">
            <Poster src={member.image} name="" className="rounded-md" />
            <p className="mt-2 truncate text-sm font-bold">{member.name}</p>
            <p className="text-dim truncate text-sm">{member.character}</p>
          </li>
        ))}
      </ul>
    </section>
  )
}
