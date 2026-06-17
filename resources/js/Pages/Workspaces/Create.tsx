import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import PageHeader from '@/Components/UI/PageHeader';
import { useTranslation } from '@/lib/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { PageProps } from '@/types';

export default function Create({
    timezones,
    industryCategories,
    regions,
    agencies,
}: PageProps<{
    timezones: string[];
    industryCategories: string[];
    regions: string[];
    agencies: Array<{ id: number; name: string }>;
}>) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        industry_category: '',
        region: '',
        timezone: timezones[0] ?? 'UTC',
        agency_id: agencies[0]?.id ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('workspaces.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('workspaces.create_title')} />

            <div className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={t('workspaces.create_title')}
                    description={t('workspaces.create_description')}
                    actions={
                        <Link
                            href={route('workspaces.index')}
                            className="sp-link text-sm"
                        >
                            {t('common.cancel')}
                        </Link>
                    }
                />

                <CalloutBanner title={t('workspaces.create_hint_title')}>
                    {t('workspaces.create_hint')}
                </CalloutBanner>

                <div className="sp-card mt-6 p-6">
                    <form onSubmit={submit} className="space-y-8">
                        {agencies.length > 0 && (
                            <div>
                                <InputLabel
                                    htmlFor="agency_id"
                                    value={t('common.agency')}
                                />
                                <select
                                    id="agency_id"
                                    className="sp-input mt-1 block w-full"
                                    value={data.agency_id}
                                    onChange={(e) =>
                                        setData('agency_id', Number(e.target.value))
                                    }
                                >
                                    {agencies.map((agency) => (
                                        <option key={agency.id} value={agency.id}>
                                            {agency.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.agency_id}
                                    className="mt-2"
                                />
                            </div>
                        )}

                        <section className="space-y-4">
                            <h3 className="text-sm font-semibold text-sp-ink">
                                {t('workspaces.create_general')}
                            </h3>

                            <div>
                                <InputLabel
                                    htmlFor="name"
                                    value={t('common.name')}
                                />
                                <TextInput
                                    id="name"
                                    className="mt-1 block w-full"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder={t(
                                        'workspaces.create_name_placeholder',
                                    )}
                                    required
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="timezone"
                                    value={t('common.timezone')}
                                />
                                <select
                                    id="timezone"
                                    className="sp-input mt-1 block w-full"
                                    value={data.timezone}
                                    onChange={(e) =>
                                        setData('timezone', e.target.value)
                                    }
                                    required
                                >
                                    {timezones.map((tz) => (
                                        <option key={tz} value={tz}>
                                            {tz}
                                        </option>
                                    ))}
                                </select>
                                <p className="mt-1 text-xs text-sp-muted">
                                    {t('workspaces.create_timezone_hint')}
                                </p>
                                <InputError
                                    message={errors.timezone}
                                    className="mt-2"
                                />
                            </div>
                        </section>

                        <section className="space-y-4 border-t border-sp-border pt-6">
                            <div>
                                <h3 className="text-sm font-semibold text-sp-ink">
                                    {t('workspaces.create_segmentation')}
                                </h3>
                                <p className="mt-1 text-xs text-sp-muted">
                                    {t('workspaces.create_segmentation_hint')}
                                </p>
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="industry_category"
                                    value={t('common.industry')}
                                />
                                <select
                                    id="industry_category"
                                    className="sp-input mt-1 block w-full"
                                    value={data.industry_category}
                                    onChange={(e) =>
                                        setData(
                                            'industry_category',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="">
                                        {t('common.select')}
                                    </option>
                                    {industryCategories.map((category) => (
                                        <option key={category} value={category}>
                                            {category}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.industry_category}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="region"
                                    value={t('common.region')}
                                />
                                <select
                                    id="region"
                                    className="sp-input mt-1 block w-full"
                                    value={data.region}
                                    onChange={(e) =>
                                        setData('region', e.target.value)
                                    }
                                >
                                    <option value="">
                                        {t('common.select')}
                                    </option>
                                    {regions.map((region) => (
                                        <option key={region} value={region}>
                                            {region}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.region}
                                    className="mt-2"
                                />
                            </div>
                        </section>

                        <div className="flex justify-end border-t border-sp-border pt-6">
                            <PrimaryButton disabled={processing}>
                                {t('workspaces.create_button')}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
