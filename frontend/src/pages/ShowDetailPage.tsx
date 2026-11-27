import { Link, useParams } from 'react-router'
import { ApiError } from '../api/http.ts'
import { BackLink } from '../components/BackLink.tsx'
import { buttonClass } from '../components/buttonClass.ts'
import { CastList } from '../components/CastList.tsx'
import { ErrorMessage } from '../components/ErrorMessage.tsx'
import { ShowFacts } from '../components/ShowFacts.tsx'
import { ShowHero, ShowHeroSkeleton } from '../components/ShowHero.tsx'
import { ShowSynopsis } from '../components/ShowSynopsis.tsx'
import { StatusMessage } from '../components/StatusMessage.tsx'
import { useBackToSearch } from '../hooks/useBackToSearch.ts'
import { useDocumentTitle } from '../hooks/useDocumentTitle.ts'
import { isValidShowId, useShow } from '../hooks/useShow.ts'
import { GUTTER } from '../layout.ts'
import { t } from '../i18n/index.ts'

export function ShowDetailPage() {
  const id = Number(useParams().id)
  const backTo = useBackToSearch()
  const { data: show, error, isPending, isFetching, refetch } = useShow(id)
  useDocumentTitle(show?.name)

  if (!isValidShowId(id) || (error instanceof ApiError && error.isNotFound)) {
    return (
      <div className="pt-10">
        <StatusMessage
          title={t.show.notFoundTitle}
          action={
            <Link to={backTo} className={buttonClass()}>
              {t.common.backToSearch}
            </Link>
          }
        >
          {t.show.notFoundBody}
        </StatusMessage>
      </div>
    )
  }

  if (error) {
    return (
      <div className={`pt-10 ${GUTTER}`}>
        <BackLink to={backTo} />
        <ErrorMessage title={t.show.errorTitle} error={error} onRetry={() => refetch()} isRetrying={isFetching} />
      </div>
    )
  }

  if (isPending) {
    return (
      <div className={`pt-10 ${GUTTER}`}>
        <BackLink to={backTo} />
        <ShowHeroSkeleton />
      </div>
    )
  }

  return (
    <>
      <ShowHero show={show}>
        <BackLink to={backTo} />
      </ShowHero>

      <div className={`grid gap-x-12 gap-y-8 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] ${GUTTER}`}>
        <ShowSynopsis text={show.summary} />
        <ShowFacts show={show} />
      </div>

      <CastList cast={show.cast} className={`mt-10 sm:mt-14 ${GUTTER}`} />
    </>
  )
}
