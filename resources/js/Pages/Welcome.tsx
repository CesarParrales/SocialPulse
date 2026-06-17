import ApplicationLogo from '@/Components/ApplicationLogo';
import LocaleSelector from '@/Components/LocaleSelector';
import { useTranslation } from '@/lib/i18n';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

const featureIcons = [
    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
];

export default function Welcome({
    auth,
}: PageProps<{ laravelVersion?: string; phpVersion?: string }>) {
    const { t } = useTranslation();

    const features = [
        {
            title: t('welcome.feature_analytics'),
            description: t('welcome.feature_analytics_desc'),
        },
        {
            title: t('welcome.feature_reports'),
            description: t('welcome.feature_reports_desc'),
        },
        {
            title: t('welcome.feature_benchmarks'),
            description: t('welcome.feature_benchmarks_desc'),
        },
    ];

    const steps = [
        { label: t('welcome.step_1'), description: t('welcome.step_1_desc') },
        { label: t('welcome.step_2'), description: t('welcome.step_2_desc') },
        { label: t('welcome.step_3'), description: t('welcome.step_3_desc') },
    ];

    return (
        <>
            <Head title={t('welcome.title')} />

            <div className="min-h-screen bg-sp-surface">
                <div className="relative overflow-hidden">
                    <div className="absolute inset-0 sp-gradient-bg opacity-90" />
                    <div className="relative mx-auto max-w-6xl px-6 py-10">
                        <header className="flex flex-wrap items-center justify-between gap-4">
                            <ApplicationLogo showWordmark variant="light" />
                            <nav
                                className="flex items-center gap-3"
                                aria-label={t('welcome.title')}
                            >
                                {auth.user ? (
                                    <Link
                                        href={route('dashboard')}
                                        className="rounded-lg bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur transition hover:bg-white/20"
                                    >
                                        {t('welcome.dashboard')}
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={route('login')}
                                            className="rounded-lg px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10"
                                        >
                                            {t('welcome.login')}
                                        </Link>
                                        <Link
                                            href={route('register')}
                                            className="rounded-lg bg-white px-4 py-2 text-sm font-medium text-sp-primary transition hover:bg-violet-50"
                                        >
                                            {t('welcome.register')}
                                        </Link>
                                    </>
                                )}
                            </nav>
                        </header>

                        <div className="mt-16 max-w-2xl">
                            <p className="text-sm font-semibold uppercase tracking-wider text-violet-200">
                                {t('welcome.eyebrow')}
                            </p>
                            <h1 className="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                                {t('welcome.tagline')}
                            </h1>
                            <p className="mt-6 text-lg leading-relaxed text-violet-100">
                                {t('welcome.description')}
                            </p>
                            {!auth.user && (
                                <div className="mt-8 flex flex-wrap gap-3">
                                    <Link
                                        href={route('register')}
                                        className="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-sp-primary shadow-lg transition hover:-translate-y-0.5 hover:bg-violet-50 hover:shadow-xl"
                                    >
                                        {t('welcome.register')}
                                    </Link>
                                    <Link
                                        href={route('login')}
                                        className="rounded-xl border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                                    >
                                        {t('welcome.login')}
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="mx-auto max-w-6xl px-6 py-12">
                    <div className="mb-10 max-w-xs">
                        <LocaleSelector variant="guest" />
                    </div>

                    <section className="mb-14">
                        <h2 className="text-2xl font-bold text-sp-ink">
                            {t('welcome.how_title')}
                        </h2>
                        <p className="mt-2 max-w-2xl text-sm text-sp-muted">
                            {t('welcome.how_description')}
                        </p>
                        <ol className="mt-8 grid gap-6 md:grid-cols-3">
                            {steps.map((step, index) => (
                                <li
                                    key={step.label}
                                    className="relative sp-card p-6"
                                >
                                    <span className="flex h-8 w-8 items-center justify-center rounded-full bg-sp-primary text-sm font-bold text-white">
                                        {index + 1}
                                    </span>
                                    <h3 className="mt-4 font-semibold text-sp-ink">
                                        {step.label}
                                    </h3>
                                    <p className="mt-2 text-sm leading-relaxed text-sp-muted">
                                        {step.description}
                                    </p>
                                </li>
                            ))}
                        </ol>
                    </section>

                    <section>
                        <h2 className="text-2xl font-bold text-sp-ink">
                            {t('welcome.features_title')}
                        </h2>
                        <div className="mt-8 grid gap-6 md:grid-cols-3">
                            {features.map((feature, index) => (
                                <FeatureCard
                                    key={feature.title}
                                    iconPath={featureIcons[index]}
                                    title={feature.title}
                                    description={feature.description}
                                />
                            ))}
                        </div>
                    </section>

                    {!auth.user && (
                        <section className="mt-14 rounded-2xl sp-gradient-bg px-8 py-10 text-center text-white">
                            <h2 className="text-2xl font-bold">
                                {t('welcome.cta_title')}
                            </h2>
                            <p className="mx-auto mt-3 max-w-xl text-violet-100">
                                {t('welcome.cta_description')}
                            </p>
                            <div className="mt-6 flex flex-wrap justify-center gap-3">
                                <Link
                                    href={route('register')}
                                    className="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-sp-primary shadow-lg transition hover:bg-violet-50"
                                >
                                    {t('welcome.register')}
                                </Link>
                                <Link
                                    href={route('login')}
                                    className="rounded-xl border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                                >
                                    {t('welcome.login')}
                                </Link>
                            </div>
                        </section>
                    )}
                </div>

                <footer className="border-t border-sp-border py-8 text-center text-sm text-sp-muted">
                    <nav className="mb-3 flex flex-wrap justify-center gap-4">
                        <Link
                            href={route('legal.privacy')}
                            className="hover:text-sp-ink"
                        >
                            {t('legal.privacy_link')}
                        </Link>
                        <Link
                            href={route('legal.terms')}
                            className="hover:text-sp-ink"
                        >
                            {t('legal.terms_link')}
                        </Link>
                    </nav>
                    © {new Date().getFullYear()} SocialPulse
                </footer>
            </div>
        </>
    );
}

function FeatureCard({
    iconPath,
    title,
    description,
}: {
    iconPath: string;
    title: string;
    description: string;
}) {
    return (
        <div className="sp-card group p-6 transition duration-200 hover:-translate-y-1 hover:shadow-sp-lg">
            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary transition group-hover:bg-sp-primary group-hover:text-white">
                <svg
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    strokeWidth={1.75}
                    aria-hidden
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d={iconPath}
                    />
                </svg>
            </div>
            <h3 className="mt-4 text-lg font-semibold text-sp-ink">{title}</h3>
            <p className="mt-3 text-sm leading-relaxed text-sp-muted">
                {description}
            </p>
        </div>
    );
}
