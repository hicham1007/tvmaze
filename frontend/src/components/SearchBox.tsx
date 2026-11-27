import { useEffect, useRef } from 'react'
import { t } from '../i18n/index.ts'

interface Props {
  value: string
  onChange: (value: string) => void
  onSubmit: () => void
}

export function SearchBox({ value, onChange, onSubmit }: Props) {
  // The page opens ready to type. If it opens with a search already in the box
  // (a reload, or coming back from a detail), that text is selected, so typing
  // starts a new search instead of adding to the old one.
  const inputRef = useRef<HTMLInputElement>(null)
  useEffect(() => {
    inputRef.current?.focus()
    inputRef.current?.select()
  }, [])

  return (
    <form
      role="search"
      className="mx-auto mt-6 max-w-2xl sm:mt-8"
      onSubmit={(event) => {
        event.preventDefault()
        onSubmit()
      }}
    >
      <label htmlFor="search" className="sr-only">
        {t.search.inputLabel}
      </label>
      <input
        id="search"
        type="search"
        value={value}
        onChange={(event) => onChange(event.target.value)}
        placeholder={t.search.inputPlaceholder}
        autoComplete="off"
        ref={inputRef}
        maxLength={100}
        // Keeps the inherited 16px on phones: iOS zooms the page when focusing smaller inputs.
        className="border-line placeholder:text-dim w-full rounded border bg-black/70 px-4 py-3 outline-none focus:border-white sm:px-5 sm:py-4 sm:text-lg"
      />
    </form>
  )
}
