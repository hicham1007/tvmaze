import { screen } from '@testing-library/react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { mockApi, renderApp } from './test/utils.tsx'

afterEach(() => vi.unstubAllGlobals())

describe('App', () => {
  it('shows a not found page, with a way to the search, for an address that does not exist', () => {
    const fetchMock = mockApi({ data: [] })

    renderApp('/nothing/here')

    expect(screen.getByText('Page not found')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Go to search' })).toHaveAttribute('href', '/')
    expect(fetchMock).not.toHaveBeenCalled()
  })
})
