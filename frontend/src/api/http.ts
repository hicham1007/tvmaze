import { t } from '../i18n/index.ts'

const BASE_URL = import.meta.env.VITE_API_URL ?? '/api'

export class ApiError extends Error {
  readonly status: number
  readonly code: string | null

  constructor(message: string, status: number, code: string | null = null) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
  }

  get isNotFound() {
    return this.status === 404
  }
}

/** Every answer of the backend: `data` on success, `message` and `code` on error. */
interface ApiBody<T> {
  data?: T
  message?: string
  code?: string
}

export async function getJson<T>(path: string, signal?: AbortSignal): Promise<T> {
  let response: Response

  try {
    response = await fetch(`${BASE_URL}${path}`, { signal, headers: { Accept: 'application/json' } })
  } catch (error) {
    // Let aborted requests (a newer search replaced this one) bubble up untouched.
    if (error instanceof DOMException && error.name === 'AbortError') throw error
    throw new ApiError('Network error', 0, 'network_error')
  }

  const body: ApiBody<T> | null = await response.json().catch(() => null)

  if (!response.ok || body?.data === undefined) {
    throw new ApiError(body?.message ?? response.statusText, response.status, body?.code ?? null)
  }

  return body.data
}

export function describeError(error: unknown): string {
  const messages: Partial<Record<string, string>> = t.errors
  const code = error instanceof ApiError ? error.code : null

  return (code && messages[code]) || t.errors.unknown
}
