import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, describe, expect, it, vi } from 'vitest'
import type { ShowDetail } from '../api/shows.ts'
import { mockApi, renderApp } from '../test/utils.tsx'

const show: ShowDetail = {
  id: 82,
  name: 'Game of Thrones',
  image: 'https://img.test/82.jpg',
  genres: ['Drama', 'Fantasy'],
  rating: 8.9,
  premiered: '2011-04-17',
  ended: '2019-05-19',
  summary: 'First paragraph.\n\nSecond paragraph.',
  type: 'Scripted',
  language: 'English',
  status: 'Ended',
  runtime: 60,
  network: 'HBO',
  country: 'United States',
  schedule: { time: '21:00', days: ['Sunday'] },
  officialSite: 'http://www.hbo.com/game-of-thrones',
  cast: [{ id: 1, name: 'Kit Harington', character: 'Jon Snow', image: null }],
}

afterEach(() => vi.unstubAllGlobals())

describe('ShowDetailPage', () => {
  it('shows the full information of the show', async () => {
    const fetchMock = mockApi({ data: show })

    renderApp('/shows/82')

    expect(screen.getByRole('status', { name: 'Loading show' })).toBeInTheDocument()
    expect(await screen.findByRole('heading', { level: 1, name: 'Game of Thrones' })).toBeInTheDocument()
    expect(fetchMock.mock.calls[0][0]).toBe('/api/shows/82')

    expect(document.title).toBe('Game of Thrones · TV Show Finder')

    expect(screen.getByText('First paragraph.')).toBeInTheDocument()
    expect(screen.getByText('Second paragraph.')).toBeInTheDocument()
    expect(screen.getByText('8.9', { exact: false })).toBeInTheDocument()
    expect(screen.getByText('Ended', { selector: 'span' })).toBeInTheDocument()
    expect(screen.getByText('2011 – 2019')).toBeInTheDocument()
    expect(screen.getByText('HBO · United States')).toBeInTheDocument()
    expect(screen.getByText('Sunday · 21:00')).toBeInTheDocument()
    expect(screen.getByText('April 17, 2011')).toBeInTheDocument()
    expect(screen.getByText('60 min')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'hbo.com' })).toHaveAttribute('href', show.officialSite)
    expect(screen.getByText('Kit Harington')).toBeInTheDocument()
    expect(screen.getByText('Jon Snow')).toBeInTheDocument()
  })

  it('leaves out the facts the show does not have', async () => {
    mockApi({
      data: {
        ...show,
        summary: null,
        rating: null,
        network: null,
        country: null,
        ended: null,
        officialSite: null,
        schedule: { time: null, days: [] },
        cast: [],
      },
    })

    renderApp('/shows/82')

    expect(await screen.findByText('This show has no summary available.')).toBeInTheDocument()
    expect(screen.getByText('Not rated')).toBeInTheDocument()
    expect(screen.queryByText('Network')).not.toBeInTheDocument()
    expect(screen.queryByText('Airs')).not.toBeInTheDocument()
    expect(screen.queryByText('Cast')).not.toBeInTheDocument()
  })

  it('says so when the show does not exist', async () => {
    mockApi({ message: 'Show 999 was not found.', code: 'show_not_found' }, 404)

    renderApp('/shows/999')

    expect(await screen.findByText('Show not found')).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: 'Try again' })).not.toBeInTheDocument()
  })

  it('does not call the API for an id that cannot exist', () => {
    const fetchMock = mockApi({ data: show })

    renderApp('/shows/abc')

    expect(screen.getByText('Show not found')).toBeInTheDocument()
    expect(fetchMock).not.toHaveBeenCalled()
  })

  it('shows the error and lets the user retry', async () => {
    mockApi({ message: 'Timeout', code: 'upstream_unreachable' }, 504)
    const user = userEvent.setup()

    renderApp('/shows/82')

    expect(await screen.findByRole('alert')).toHaveTextContent('is taking too long to respond')

    mockApi({ data: show })
    await user.click(screen.getByRole('button', { name: 'Try again' }))

    expect(await screen.findByRole('heading', { level: 1, name: 'Game of Thrones' })).toBeInTheDocument()
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()
  })

  it('goes back to an empty search when the detail was opened directly', async () => {
    mockApi({ data: show })

    renderApp('/shows/82')

    expect(await screen.findByRole('link', { name: /Back to search/ })).toHaveAttribute('href', '/')
  })
})
