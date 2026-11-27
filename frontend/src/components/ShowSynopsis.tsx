import { t } from '../i18n/index.ts'

/** The summary arrives as plain text, with paragraphs separated by a blank line. */
export function ShowSynopsis({ text }: { text: string | null }) {
  return (
    <section className="max-w-prose space-y-4 leading-relaxed break-words sm:text-lg">
      <h2 className="sr-only">{t.show.summary}</h2>
      {text ? (
        text.split('\n\n').map((paragraph, index) => <p key={index}>{paragraph}</p>)
      ) : (
        <p className="text-dim">{t.show.noSummary}</p>
      )}
    </section>
  )
}
