import { ErrorMessage } from '../components/ErrorMessage.tsx'
import { SearchBox } from '../components/SearchBox.tsx'
import { ShowGrid, ShowGridSkeleton } from '../components/ShowGrid.tsx'
import { StatusMessage } from '../components/StatusMessage.tsx'
import { useDocumentTitle } from '../hooks/useDocumentTitle.ts'
import { useSearchTerm } from '../hooks/useSearchTerm.ts'
import { useShowSearch } from '../hooks/useShowSearch.ts'
import { GUTTER } from '../layout.ts'
import { t } from '../i18n/index.ts'

export function SearchPage() {
  const { input, setInput, query, submit } = useSearchTerm()
  useDocumentTitle(query ? t.search.pageTitle(query) : null)

  return (
    <>
      <section className="bg-[radial-gradient(ellipse_80%_100%_at_50%_0%,rgba(229,9,20,0.35),transparent_70%)]">
        <div className={`pt-10 pb-8 text-center sm:pt-24 sm:pb-10 ${GUTTER}`}>
          <h1 className="mx-auto max-w-3xl text-3xl font-black tracking-tight text-balance min-[400px]:text-4xl sm:text-6xl">
            {t.search.heading}
          </h1>
          <p className="text-muted mt-3 text-balance sm:mt-4 sm:text-xl">{t.search.tagline}</p>

          <SearchBox value={input} onChange={setInput} onSubmit={submit} />
        </div>
      </section>

      <div className={GUTTER}>
        <SearchResults query={query} />
      </div>
    </>
  )
}

function SearchResults({ query }: { query: string }) {
  const { data, error, isPending, isFetching, isPlaceholderData, refetch } = useShowSearch(query)

  if (query.length === 0) {
    return <StatusMessage title={t.search.idleTitle}>{t.search.idleBody}</StatusMessage>
  }

  if (error) {
    return <ErrorMessage title={t.search.errorTitle} error={error} onRetry={() => refetch()} isRetrying={isFetching} />
  }

  if (isPending) {
    return (
      <div role="status" aria-label={t.search.loading} className="mt-4">
        <ShowGridSkeleton />
      </div>
    )
  }

  if (data.length === 0) {
    return <StatusMessage title={t.search.emptyTitle}>{t.search.emptyBody(query)}</StatusMessage>
  }

  return (
    <section className="mt-4" aria-busy={isPlaceholderData}>
      <div className="mb-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
        <h2 className="min-w-0 text-xl font-bold break-words sm:text-2xl">{t.search.resultsHeading(query)}</h2>
        <p className="text-dim shrink-0 text-sm" role="status">
          {isPlaceholderData ? t.search.searching : t.search.resultCount(data.length)}
        </p>
      </div>
      <ShowGrid shows={data} dimmed={isPlaceholderData} />
    </section>
  )
}
