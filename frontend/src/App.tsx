import { Route, Routes, useLocation } from 'react-router'
import { ErrorBoundary } from './components/ErrorBoundary.tsx'
import { GUTTER } from './layout.ts'
import { NotFoundPage } from './pages/NotFoundPage.tsx'
import { SearchPage } from './pages/SearchPage.tsx'
import { ShowDetailPage } from './pages/ShowDetailPage.tsx'
import { t } from './i18n/index.ts'

export default function App() {
  const { pathname } = useLocation()

  return (
    <div className="flex min-h-screen flex-col">
      <main className="flex-1 pb-12 sm:pb-20">
        {/* Keyed by path: navigating away from a broken page clears its error. */}
        <ErrorBoundary key={pathname}>
          <Routes>
            <Route path="/" element={<SearchPage />} />
            <Route path="/shows/:id" element={<ShowDetailPage />} />
            <Route path="*" element={<NotFoundPage />} />
          </Routes>
        </ErrorBoundary>
      </main>

      <footer className={`text-dim py-8 text-sm ${GUTTER}`}>
        {t.app.dataSource}{' '}
        <a
          href="https://www.tvmaze.com"
          target="_blank"
          rel="noreferrer"
          className="hover:text-muted -my-2 inline-block py-2 underline"
        >
          TVMaze
        </a>
      </footer>
    </div>
  )
}
