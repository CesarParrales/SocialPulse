import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Card from '@/Components/UI/Card';
import FlashAlert from '@/Components/UI/FlashAlert';
import PageHeader from '@/Components/UI/PageHeader';
import { useTranslation } from '@/lib/i18n';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout>
            <Head title={t('profile.title')} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={t('profile.title')}
                    description={t('profile.description')}
                />

                {flash.success && <FlashAlert message={flash.success} />}

                {status === 'verification-link-sent' && (
                    <FlashAlert message={t('profile.verification_sent')} />
                )}

                <Card>
                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                    />
                </Card>

                <Card>
                    <UpdatePasswordForm />
                </Card>

                <Card className="border-red-200 bg-red-50/30">
                    <DeleteUserForm />
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
