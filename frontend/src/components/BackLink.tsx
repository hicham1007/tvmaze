import { Link, type To } from 'react-router'
import { t } from '../i18n/index.ts'

export function BackLink({ to }: { to: To }) {
  return (
    // A solid pill, so it stays readable over any backdrop the poster produces.
    <Link
      to={to}
      className="inline-flex items-center gap-2 rounded-full border border-white/40 bg-black/60 px-4 py-2 font-semibold text-white backdrop-blur-sm transition-colors hover:border-white hover:bg-white hover:text-black"
    >
      <span aria-hidden="true" className="text-lg leading-none">
        ←
      </span>
      {t.common.backToSearch}
    </Link>
  )
}
