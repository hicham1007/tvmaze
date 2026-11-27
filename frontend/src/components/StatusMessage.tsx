import type { ReactNode } from 'react'

interface Props {
  title: string
  children?: ReactNode
  action?: ReactNode
  tone?: 'neutral' | 'error'
}

export function StatusMessage({ title, children, action, tone = 'neutral' }: Props) {
  return (
    <div role={tone === 'error' ? 'alert' : 'status'} className="mx-auto mt-10 max-w-md px-4 text-center sm:mt-16">
      <h2 className="text-xl font-bold text-balance break-words sm:text-2xl">{title}</h2>
      {children && <p className="text-muted mt-2 break-words">{children}</p>}
      {action && <div className="mt-6">{action}</div>}
    </div>
  )
}
