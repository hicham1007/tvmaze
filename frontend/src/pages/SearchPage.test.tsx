import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, describe, expect, it, vi } from 'vitest'
import type { ShowSummary } from '../api/shows.ts'
import { mockApi, renderApp } from '../test/utils.tsx'

const girls: ShowSummary = {
  id: 139,
  name: 'Girls',
  image: 'https://img.test/139.jpg',
  genres: ['Drama', 'Romance'],
  rating: 6.5,
  premiered: '2012-04-15',
}

const unrated: ShowSummary = { id: 2, name: 'Obscure Show', image: null, genres: [], rating: null, premiered: null }

afterEach(() => vi.unstubAllGlobals())

describe('SearchPage', () => {
  it('invites to type and calls nothing when there is no search', () => {
    const fetchMock = mockApi({ data: [] })

    renderApp('/')

    expect(screen.getByText('Start typing')).toBeInTheDocument()
    expect(document.title).toBe('TV Show Finder')
    expect(fetchMock).not.toHaveBeenCalled()
  })

  it('shows a card per result with name, genres and rating', async () => {
    mockApi({ data: [girls, unrated] })

    renderApp('/?q=girls')

    expect(screen.getByRole('status', { name: 'Loading shows' })).toBeInTheDocument()

    const card = await screen.findByRole('link', { name: /Girls/ })
    expect(card).toHaveAttribute('href', '/shows/139')
    expect(card).toHaveTextContent('Drama')
    expect(card).toHaveTextContent('Romance')
    expect(card).toHaveTextContent('6.5')
    expect(card).toHaveTextContent('2012')

    const bare = screen.getByRole('link', { name: /Obscure Show/ })
    expect(bare).toHaveTextContent('Not rated')
    // Without a poster, the card says so and shows the name in its place.
    expect(bare).toHaveTextContent('no image')
    expect(bare.querySelector('img')).toBeNull()
    expect(card.querySelector('img')).toHaveAttribute('src', girls.image)
    expect(screen.getByText('2 results')).toBeInTheDocument()
    expect(document.title).toBe('“girls” · TV Show Finder')
  })

  it('searches what the user types, once, after they stop typing', async () => {
    const fetchMock = mockApi({ data: [girls] })
    const user = userEvent.setup()

    renderApp('/')
    await user.type(screen.getByRole('searchbox'), 'the girls')

    expect(await screen.findByRole('link', { name: /Girls/ })).toBeInTheDocument()
    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(fetchMock.mock.calls[0][0]).toBe('/api/shows?q=the%20girls')
  })

  it('searches at once when Enter is pressed, without waiting for the typing pause', async () => {
    const fetchMock = mockApi({ data: [girls] })
    const user = userEvent.setup()

    renderApp('/')
    const startedAt = performance.now()
    await user.type(screen.getByRole('searchbox'), 'girls{Enter}')

    // Well under the 350 ms pause.
    await waitFor(() => expect(fetchMock).toHaveBeenCalledWith('/api/shows?q=girls', expect.anything()), {
      timeout: 150,
    })
    expect(performance.now() - startedAt).toBeLessThan(300)

    // The pause ending afterwards does not search the same text again.
    await new Promise((resolve) => setTimeout(resolve, 500))
    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(screen.getByRole('link', { name: /Girls/ })).toBeInTheDocument()
  })

  it('replaces the previous search, instead of adding to it, when typing after a reload', async () => {
    const fetchMock = mockApi({ data: [girls] })
    const user = userEvent.setup()

    // The page is loaded with a search already in the URL, as after a refresh.
    renderApp('/?q=dark')
    const input = screen.getByRole('searchbox')
    expect(input).toHaveValue('dark')
    expect(input).toHaveFocus()

    // The user just starts typing, without clicking or clearing the box first.
    await user.keyboard('girls')

    expect(input).toHaveValue('girls')
    await waitFor(() => expect(fetchMock).toHaveBeenLastCalledWith('/api/shows?q=girls', expect.anything()))
  })

  it('does not search when only spaces are typed', async () => {
    const fetchMock = mockApi({ data: [] })
    const user = userEvent.setup()

    renderApp('/')
    await user.type(screen.getByRole('searchbox'), '   ')
    await new Promise((resolve) => setTimeout(resolve, 500))

    expect(screen.getByText('Start typing')).toBeInTheDocument()
    expect(fetchMock).not.toHaveBeenCalled()
  })

  it('tells the user when there are no results', async () => {
    mockApi({ data: [] })

    renderApp('/?q=zzzzzz')

    expect(await screen.findByText('No results')).toBeInTheDocument()
    expect(screen.getByText(/“zzzzzz”/)).toBeInTheDocument()
  })

  it('shows the error and lets the user retry', async () => {
    mockApi({ message: 'Upstream down', code: 'upstream_error' }, 502)
    const user = userEvent.setup()

    renderApp('/?q=girls')

    expect(await screen.findByRole('alert')).toHaveTextContent('The show provider is not available right now.')

    mockApi({ data: [girls] })
    await user.click(screen.getByRole('button', { name: 'Try again' }))

    expect(await screen.findByRole('link', { name: /Girls/ })).toBeInTheDocument()
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()
  })

  it('shows a generic error, with retry, when the backend fails unexpectedly', async () => {
    mockApi({ message: 'Server Error' }, 500)

    renderApp('/?q=girls')

    expect(await screen.findByRole('alert')).toHaveTextContent('An unexpected error occurred.')
    expect(screen.getByRole('button', { name: 'Try again' })).toBeInTheDocument()
  })

  it('copes with an error page that is not JSON, as a proxy sends when the backend is down', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async () => new Response('<html><h1>502 Bad Gateway</h1></html>', { status: 502 })),
    )

    renderApp('/?q=girls')

    expect(await screen.findByRole('alert')).toHaveTextContent('An unexpected error occurred.')
  })

  it('reports a connection problem when the backend cannot be reached', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new TypeError('Failed to fetch')))

    renderApp('/?q=girls')

    expect(await screen.findByRole('alert')).toHaveTextContent('Could not connect to the server')
  })

  it('opens the detail when a card is clicked', async () => {
    const fetchMock = mockApi({ data: [girls] })
    const user = userEvent.setup()

    renderApp('/?q=girls')
    const card = await screen.findByRole('link', { name: /Girls/ })

    // The detail request stays pending: this test is only about the navigation.
    fetchMock.mockReturnValue(new Promise(() => {}))
    await user.click(card)

    await waitFor(() => expect(screen.getByRole('status', { name: 'Loading show' })).toBeInTheDocument())
    expect(fetchMock).toHaveBeenLastCalledWith('/api/shows/139', expect.anything())
    expect(screen.getByRole('link', { name: /Back to search/ })).toHaveAttribute('href', '/?q=girls')
  })
})
