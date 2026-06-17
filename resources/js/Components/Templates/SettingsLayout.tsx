import PageHeader from '@/Components/UI/PageHeader';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { ReactNode } from 'react';

export default function SettingsLayout({
    headTitle,
    title,
    description,
    actions,
    subNav,
    children,
}: {
    headTitle: string;
    title: string;
    description?: string;
    actions?: ReactNode;
    subNav?: ReactNode;
    children: ReactNode;
}) {
    return (
        <AuthenticatedLayout>
            <Head title={headTitle} />

            <div className="mx-auto w-full min-w-0 max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={title}
                    description={description}
                    actions={actions}
                />
                {subNav}
                {children}
            </div>
        </AuthenticatedLayout>
    );
}
