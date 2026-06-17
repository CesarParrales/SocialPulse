import ApplicationLogo from '@/Components/ApplicationLogo';
import LocaleSelector from '@/Components/LocaleSelector';
import { useTranslation } from '@/lib/i18n';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    const { t } = useTranslation();

    return (
        <div className="flex min-h-screen">
            <div className="relative hidden w-1/2 sp-gradient-bg lg:flex lg:flex-col lg:justify-between lg:p-12">
                <Link href="/">
                    <ApplicationLogo showWordmark variant="light" />
                </Link>
                <div className="max-w-md">
                    <h1 className="text-3xl font-bold leading-tight text-white">
                        {t('home.guest_headline')}
                    </h1>
                    <p className="mt-4 text-lg text-violet-100">
                        {t('home.guest_subline')}
                    </p>
                </div>
                <p className="text-sm text-violet-200/80">
                    © {new Date().getFullYear()} SocialPulse
                </p>
                <div
                    className="pointer-events-none absolute inset-0 opacity-20"
                    aria-hidden="true"
                >
                    <svg className="h-full w-full" viewBox="0 0 800 800">
                        <path
                            d="M0 400 Q200 200 400 400 T800 400"
                            fill="none"
                            stroke="white"
                            strokeWidth="2"
                        />
                        <path
                            d="M0 500 Q200 300 400 500 T800 500"
                            fill="none"
                            stroke="white"
                            strokeWidth="1.5"
                        />
                    </svg>
                </div>
            </div>

            <div className="flex w-full flex-col justify-center bg-sp-surface px-6 py-12 lg:w-1/2 lg:px-16">
                <div className="mx-auto w-full max-w-md">
                    <div className="mb-8 lg:hidden">
                        <Link href="/">
                            <ApplicationLogo showWordmark />
                        </Link>
                    </div>
                    <LocaleSelector variant="guest" />
                    <div className="sp-card p-8 shadow-sp-lg">{children}</div>
                    <nav className="mt-6 flex flex-wrap justify-center gap-4 text-xs text-sp-muted">
                        <Link href={route('legal.privacy')} className="hover:text-sp-ink">
                            {t('legal.privacy_link')}
                        </Link>
                        <Link href={route('legal.terms')} className="hover:text-sp-ink">
                            {t('legal.terms_link')}
                        </Link>
                        <Link href={route('login')} className="hover:text-sp-ink">
                            {t('auth.login')}
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
    );
}
