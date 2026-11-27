import { Link } from 'react-router'
import { buttonClass } from '../components/buttonClass.ts'
import { StatusMessage } from '../components/StatusMessage.tsx'
import { t } from '../i18n/index.ts'

export function NotFoundPage() {
  return (
    <div className="pt-24">
      <StatusMessage
        title={t.notFound.title}
        action={
          <Link to="/" className={buttonClass()}>
            {t.notFound.action}
          </Link>
        }
      >
        {t.notFound.body}
      </StatusMessage>
    </div>
  )
}
