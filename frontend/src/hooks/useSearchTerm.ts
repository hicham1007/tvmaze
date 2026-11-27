import { useCallback, useEffect, useState } from 'react'
import { useSearchParams } from 'react-router'

const TYPING_PAUSE_MS = 350

export function useSearchTerm() {
  const [searchParams, setSearchParams] = useSearchParams()
  const query = searchParams.get('q') ?? ''

  const [input, setInput] = useState(query)

  const search = useCallback(
    (text: string) => {
      const next = text.trim()
      setSearchParams(
        (current) => {
          if ((current.get('q') ?? '') === next) return current
          return new URLSearchParams(next ? { q: next } : {})
        },
        { replace: true },
      )
    },
    [setSearchParams],
  )

  useEffect(() => {
    const timer = setTimeout(() => search(input), TYPING_PAUSE_MS)
    return () => clearTimeout(timer)
  }, [input, search])

  const submit = useCallback(() => search(input), [search, input])

  return { input, setInput, query, submit }
}
