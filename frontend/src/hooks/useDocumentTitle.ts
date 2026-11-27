import { useEffect } from 'react'
import { t } from '../i18n/index.ts'

export function useDocumentTitle(title?: string | null) {
  useEffect(() => {
    document.title = title ? t.app.pageTitle(title) : t.app.name
  }, [title])
}
