import { describeError } from '../api/http.ts'
import { Button } from './Button.tsx'
import { StatusMessage } from './StatusMessage.tsx'
import { t } from '../i18n/index.ts'

interface Props {
  title: string
  error: unknown
  onRetry: () => void
  isRetrying: boolean
}

export function ErrorMessage({ title, error, onRetry, isRetrying }: Props) {
  return (
    <StatusMessage
      tone="error"
      title={title}
      action={
        <Button variant="danger" onClick={onRetry} disabled={isRetrying}>
          {isRetrying ? t.common.retrying : t.common.retry}
        </Button>
      }
    >
      {describeError(error)}
    </StatusMessage>
  )
}
