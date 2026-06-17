import ApplicationLogo from '@/Components/ApplicationLogo';
import LocaleSelector from '@/Components/LocaleSelector';
import { useTranslation } from '@/lib/i18n';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

interface LegalSection {
    heading: string;
    body: string;
}

function sectionId(heading: string): string {
    return heading
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-');
}

export default function Show({
    document,
    title,
    updated,
    intro,
    sections,
    contactEmail,
}: PageProps<{
    document: string;
    title: string;
    updated: string;
    intro: string;
    sections: LegalSection[];
    contactEmail: string;
}>) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={title} />

            <div className="min-h-screen bg-sp-surface">
                <header className="border-b border-sp-border bg-white">
                    <div className="mx-auto flex max-w-3xl flex-wrap items-center justify-between gap-4 px-6 py-4">
                        <Link href="/">
                            <ApplicationLogo showWordmark />
                        </Link>
                        <div className="flex items-center gap-4">
                            <LocaleSelector variant="guest" />
                            <Link
                                href={route('login')}
                                className="sp-link text-sm"
                            >
                                {t('welcome.login')}
                            </Link>
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-10">
                    <nav className="mb-6 flex flex-wrap gap-3 text-sm text-sp-muted">
                        <Link href="/" className="hover:text-sp-ink">
                            {t('legal.back_home')}
                        </Link>
                        {document !== 'privacy' && (
                            <>
                                <span aria-hidden>·</span>
                                <Link
                                    href={route('legal.privacy')}
                                    className="hover:text-sp-ink"
                                >
                                    {t('legal.privacy_link')}
                                </Link>
                            </>
                        )}
                        {document !== 'terms' && (
                            <>
                                <span aria-hidden>·</span>
                                <Link
                                    href={route('legal.terms')}
                                    className="hover:text-sp-ink"
                                >
                                    {t('legal.terms_link')}
                                </Link>
                            </>
                        )}
                    </nav>

                    <p className="text-sm text-sp-muted">
                        {t('legal.last_updated')}: {updated}
                    </p>
                    <h1 className="mt-2 text-3xl font-bold tracking-tight text-sp-ink">
                        {title}
                    </h1>
                    <p className="mt-4 text-base leading-relaxed text-sp-muted">
                        {intro}
                    </p>

                    {sections.length > 1 && (
                        <nav
                            aria-label={t('legal.table_of_contents')}
                            className="mt-8 rounded-xl border border-sp-border bg-white p-5"
                        >
                            <h2 className="text-sm font-semibold uppercase tracking-wider text-sp-muted">
                                {t('legal.table_of_contents')}
                            </h2>
                            <ol className="mt-3 space-y-2 text-sm">
                                {sections.map((section) => (
                                    <li key={section.heading}>
                                        <a
                                            href={`#${sectionId(section.heading)}`}
                                            className="text-sp-primary hover:underline"
                                        >
                                            {section.heading}
                                        </a>
                                    </li>
                                ))}
                            </ol>
                        </nav>
                    )}

                    <div className="mt-10 space-y-10">
                        {sections.map((section) => (
                            <section
                                key={section.heading}
                                id={sectionId(section.heading)}
                                className="scroll-mt-6"
                            >
                                <h2 className="text-lg font-semibold text-sp-ink">
                                    {section.heading}
                                </h2>
                                <p className="mt-3 whitespace-pre-line text-sm leading-relaxed text-sp-muted">
                                    {section.body}
                                </p>
                            </section>
                        ))}
                    </div>

                    <aside className="mt-12 rounded-xl border border-sp-border bg-white p-5">
                        <p className="text-sm font-medium text-sp-ink">
                            {t('legal.contact')}
                        </p>
                        <a
                            href={`mailto:${contactEmail}`}
                            className="mt-2 inline-block text-sm text-sp-primary hover:underline"
                        >
                            {contactEmail}
                        </a>
                    </aside>
                </main>

                <footer className="border-t border-sp-border py-6 text-center text-sm text-sp-muted">
                    © {new Date().getFullYear()} SocialPulse
                </footer>
            </div>
        </>
    );
}
