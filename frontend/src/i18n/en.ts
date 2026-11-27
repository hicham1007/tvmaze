export const en = {
  app: {
    name: 'TV Show Finder',
    pageTitle: (page: string) => `${page} · TV Show Finder`,
    dataSource: 'Data from',
  },

  common: {
    retry: 'Try again',
    retrying: 'Retrying…',
    backToSearch: 'Back to search',
  },

  notFound: {
    title: 'Page not found',
    body: 'The address you opened does not exist.',
    action: 'Go to search',
  },

  crash: {
    title: 'Something went wrong',
    body: 'An unexpected error occurred while showing this page.',
    action: 'Reload the page',
  },

  search: {
    heading: 'Find your next show',
    tagline: 'Search by title and discover what it is about, where it airs and who is in it.',
    inputLabel: 'Show name',
    inputPlaceholder: 'Titles: Breaking Bad, The Office, Dark…',
    pageTitle: (query: string) => `“${query}”`,
    idleTitle: 'Start typing',
    idleBody: 'Results will appear here as you type.',
    loading: 'Loading shows',
    searching: 'Searching…',
    errorTitle: 'The shows could not be loaded',
    emptyTitle: 'No results',
    emptyBody: (query: string) => `We could not find any show for “${query}”. Try another name.`,
    resultsHeading: (query: string) => `Results for “${query}”`,
    resultCount: (count: number) => `${count} ${count === 1 ? 'result' : 'results'}`,
  },

  show: {
    loading: 'Loading show',
    errorTitle: 'The show could not be loaded',
    notFoundTitle: 'Show not found',
    notFoundBody: 'There is no show with this identifier.',
    officialSite: 'Official site',
    summary: 'Summary',
    noSummary: 'This show has no summary available.',
    cast: 'Cast',
    genres: 'Genres',
    noImage: 'no image',
    noRating: 'Not rated',
    ratingLabel: (rating: string) => `Rated ${rating} out of 10`,
    runtime: (minutes: number) => `${minutes} min`,
    facts: {
      network: 'Network',
      schedule: 'Airs',
      premiered: 'Premiered',
      ended: 'Ended',
      language: 'Language',
      type: 'Type',
    },
  },

  errors: {
    network_error: 'Could not connect to the server. Check your connection.',
    upstream_unreachable: 'The show provider is taking too long to respond.',
    upstream_rate_limited: 'Too many requests right now. Wait a few seconds.',
    upstream_error: 'The show provider is not available right now.',
    unknown: 'An unexpected error occurred.',
  },

  /** BCP 47 tag used to write dates. */
  dateLocale: 'en-US',
}
