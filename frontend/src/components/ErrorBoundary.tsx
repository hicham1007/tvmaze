import { Component, type ErrorInfo, type ReactNode } from 'react'
import { Button } from './Button.tsx'
import { StatusMessage } from './StatusMessage.tsx'
import { t } from '../i18n/index.ts'

interface Props {
  children: ReactNode
}

interface State {
  failed: boolean
}

export class ErrorBoundary extends Component<Props, State> {
  state: State = { failed: false }

  static getDerivedStateFromError(): State {
    return { failed: true }
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('Unexpected error while rendering', error, info.componentStack)
  }

  render() {
    if (!this.state.failed) return this.props.children

    return (
      <div className="pt-24">
        <StatusMessage
          tone="error"
          title={t.crash.title}
          action={
            <Button variant="danger" onClick={() => window.location.reload()}>
              {t.crash.action}
            </Button>
          }
        >
          {t.crash.body}
        </StatusMessage>
      </div>
    )
  }
}
