import ApplicationLogo from '@/Components/ApplicationLogo';
import PrimaryButton from '@/Components/PrimaryButton';
import { useTranslation } from '@/lib/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

const statusKeys: Record<number, { title: string; description: string }> = {
    403: {
        title: 'errors.403_title',
        description: 'errors.403_description',
    },
    404: {
        title: 'errors.404_title',
        description: 'errors.404_description',
    },
    500: {
        title: 'errors.500_title',
        description: 'errors.500_description',
    },
    503: {
        title: 'errors.503_title',
        description: 'errors.503_description',
    },
};

export default function Error({ status }: PageProps<{ status: number }>) {
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const keys = statusKeys[status] ?? statusKeys[404];
    const isAuthenticated = auth.user !== null;

    const content = (
        <>
            <Head title={t(keys.title)} />

            <div className="flex flex-col items-center justify-center px-6 py-16 text-center">
                <p className="text-6xl font-bold tabular-nums text-sp-primary/20">
                    {status}
                </p>
                <h1 className="mt-4 text-2xl font-semibold text-sp-ink">
                    {t(keys.title)}
                </h1>
                <p className="mt-3 max-w-md text-sm leading-relaxed text-sp-muted">
                    {t(keys.description)}
                </p>
                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    {isAuthenticated ? (
                        <Link href={route('dashboard')}>
                            <PrimaryButton type="button">
                                {t('errors.back_dashboard')}
                            </PrimaryButton>
                        </Link>
                    ) : (
                        <Link href={route('login')}>
                            <PrimaryButton type="button">
                                {t('errors.back_login')}
                            </PrimaryButton>
                        </Link>
                    )}
                    <Link
                        href="/"
                        className="inline-flex items-center rounded-lg border border-sp-border bg-white px-4 py-2.5 text-sm font-semibold text-sp-ink shadow-sm transition hover:bg-sp-surface"
                    >
                        {t('legal.back_home')}
                    </Link>
                </div>
            </div>
        </>
    );

    if (isAuthenticated) {
        return <AuthenticatedLayout>{content}</AuthenticatedLayout>;
    }

    return (
        <div className="min-h-screen bg-sp-surface">
            <header className="border-b border-sp-border bg-white px-6 py-4">
                <Link href="/">
                    <ApplicationLogo showWordmark />
                </Link>
            </header>
            <main>{content}</main>
        </div>
    );
}
