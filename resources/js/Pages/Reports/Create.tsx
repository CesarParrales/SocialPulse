import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import EmptyState from '@/Components/UI/EmptyState';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { useTranslation } from '@/lib/i18n';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { PageProps, Workspace } from '@/types';

type Option = { key: string; label: string };
type PeriodOption = { value: string; label: string };

export default function Create({
    workspace,
    periodOptions,
    sectionOptions,
    metricOptions,
    hasConnectedAssets,
}: PageProps<{
    workspace: Pick<Workspace, 'id' | 'name'>;
    periodOptions: PeriodOption[];
    sectionOptions: Option[];
    metricOptions: Option[];
    hasConnectedAssets: boolean;
}>) {
    const { t } = useTranslation();

    const defaultSections = Object.fromEntries(
        sectionOptions.map(({ key }) => [key, key !== 'comparisons']),
    );
    const defaultMetrics = Object.fromEntries(
        metricOptions.map(({ key }) => [key, true]),
    );

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        title: t('reports.default_title', { name: workspace.name }),
        period: '30d',
        from: '',
        to: '',
        primary_color: '#6D28D9',
        secondary_color: '#EC4899',
        logo: null as File | null,
        sections: defaultSections as Record<string, boolean>,
        metrics: defaultMetrics as Record<string, boolean>,
    });

    const [logoPreview, setLogoPreview] = useState<string | null>(null);

    const setAllSections = (value: boolean) => {
        setData(
            'sections',
            Object.fromEntries(sectionOptions.map(({ key }) => [key, value])),
        );
    };

    const setAllMetrics = (value: boolean) => {
        setData(
            'metrics',
            Object.fromEntries(metricOptions.map(({ key }) => [key, value])),
        );
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('workspaces.reports.store', workspace.id), {
            forceFormData: true,
        });
    };

    return (
        <WorkspaceLayout
            headTitle={`${t('reports.create_title')} — ${workspace.name}`}
            title={t('reports.create_title')}
            description={workspace.name}
            workspace={workspace}
            active="reports"
            actions={
                <Link
                    href={route('workspaces.reports.index', workspace.id)}
                    className="sp-link text-sm"
                >
                    {t('common.cancel')}
                </Link>
            }
        >
                {!hasConnectedAssets ? (
                    <EmptyState
                        title={t('reports.no_connections_title')}
                        description={t('reports.no_connections_description')}
                        action={{
                            label: t('dashboard.connect_accounts'),
                            href: route(
                                'workspaces.connections.index',
                                workspace.id,
                            ),
                        }}
                    />
                ) : (
                    <form onSubmit={submit} className="space-y-6">
                        <div className="sp-card overflow-hidden p-0">
                            <div
                                className="h-2"
                                style={{
                                    background: `linear-gradient(90deg, ${data.primary_color}, ${data.secondary_color})`,
                                }}
                            />
                            <div className="space-y-6 p-6">
                                <p className="text-sm text-sp-muted">
                                    {t('reports.brand_preview')}
                                </p>

                                <div>
                                    <InputLabel
                                        htmlFor="title"
                                        value={t('reports.report_title')}
                                    />
                                    <TextInput
                                        id="title"
                                        className="mt-1 block w-full"
                                        value={data.title}
                                        onChange={(e) =>
                                            setData('title', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.title}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="name"
                                        value={t('reports.internal_name')}
                                    />
                                    <TextInput
                                        id="name"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) =>
                                            setData('name', e.target.value)
                                        }
                                    />
                                    <InputError
                                        message={errors.name}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="period"
                                        value={t('dashboard.period')}
                                    />
                                    <select
                                        id="period"
                                        className="sp-input mt-1 block w-full"
                                        value={data.period}
                                        onChange={(e) =>
                                            setData('period', e.target.value)
                                        }
                                    >
                                        {periodOptions.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.period}
                                        className="mt-2"
                                    />
                                </div>

                                {data.period === 'custom' && (
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <InputLabel
                                                htmlFor="from"
                                                value={t('common.from')}
                                            />
                                            <TextInput
                                                id="from"
                                                type="date"
                                                className="mt-1 block w-full"
                                                value={data.from}
                                                onChange={(e) =>
                                                    setData('from', e.target.value)
                                                }
                                            />
                                            <InputError
                                                message={errors.from}
                                                className="mt-2"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                htmlFor="to"
                                                value={t('common.to')}
                                            />
                                            <TextInput
                                                id="to"
                                                type="date"
                                                className="mt-1 block w-full"
                                                value={data.to}
                                                onChange={(e) =>
                                                    setData('to', e.target.value)
                                                }
                                            />
                                            <InputError
                                                message={errors.to}
                                                className="mt-2"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="sp-card space-y-6 p-6">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel
                                        htmlFor="primary_color"
                                        value={t('reports.primary_color')}
                                    />
                                    <input
                                        id="primary_color"
                                        type="color"
                                        className="mt-1 h-10 w-full cursor-pointer rounded-lg border border-sp-border"
                                        value={data.primary_color}
                                        onChange={(e) =>
                                            setData(
                                                'primary_color',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="secondary_color"
                                        value={t('reports.secondary_color')}
                                    />
                                    <input
                                        id="secondary_color"
                                        type="color"
                                        className="mt-1 h-10 w-full cursor-pointer rounded-lg border border-sp-border"
                                        value={data.secondary_color}
                                        onChange={(e) =>
                                            setData(
                                                'secondary_color',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="logo"
                                    value={t('reports.client_logo')}
                                />
                                <input
                                    id="logo"
                                    type="file"
                                    accept="image/*"
                                    className="mt-1 block w-full text-sm text-sp-muted"
                                    onChange={(e) => {
                                        const file =
                                            e.target.files?.[0] ?? null;
                                        setData('logo', file);
                                        setLogoPreview(
                                            file
                                                ? URL.createObjectURL(file)
                                                : null,
                                        );
                                    }}
                                />
                                {logoPreview && (
                                    <img
                                        src={logoPreview}
                                        alt={t('reports.logo_preview')}
                                        className="mt-2 h-12 object-contain"
                                    />
                                )}
                                <InputError
                                    message={errors.logo}
                                    className="mt-2"
                                />
                            </div>
                        </div>

                        <div className="sp-card space-y-4 p-6">
                            <fieldset>
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <legend className="text-sm font-medium text-sp-ink">
                                        {t('reports.sections')}
                                    </legend>
                                    <div className="flex gap-2 text-xs">
                                        <button
                                            type="button"
                                            className="sp-link"
                                            onClick={() => setAllSections(true)}
                                        >
                                            {t('reports.select_all')}
                                        </button>
                                        <span className="text-sp-muted">·</span>
                                        <button
                                            type="button"
                                            className="sp-link"
                                            onClick={() =>
                                                setAllSections(false)
                                            }
                                        >
                                            {t('reports.deselect_all')}
                                        </button>
                                    </div>
                                </div>
                                <div className="mt-3 space-y-2">
                                    {sectionOptions.map(({ key, label }) => (
                                        <label
                                            key={key}
                                            className="flex items-center gap-2"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={
                                                    data.sections[key] ?? false
                                                }
                                                onChange={(e) =>
                                                    setData('sections', {
                                                        ...data.sections,
                                                        [key]: e.target.checked,
                                                    })
                                                }
                                                className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                            />
                                            <span className="text-sm text-sp-muted">
                                                {label}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>

                            <fieldset>
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <legend className="text-sm font-medium text-sp-ink">
                                        {t('reports.metrics')}
                                    </legend>
                                    <div className="flex gap-2 text-xs">
                                        <button
                                            type="button"
                                            className="sp-link"
                                            onClick={() => setAllMetrics(true)}
                                        >
                                            {t('reports.select_all')}
                                        </button>
                                        <span className="text-sp-muted">·</span>
                                        <button
                                            type="button"
                                            className="sp-link"
                                            onClick={() =>
                                                setAllMetrics(false)
                                            }
                                        >
                                            {t('reports.deselect_all')}
                                        </button>
                                    </div>
                                </div>
                                <div className="mt-3 grid gap-2 sm:grid-cols-2">
                                    {metricOptions.map(({ key, label }) => (
                                        <label
                                            key={key}
                                            className="flex items-center gap-2"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={
                                                    data.metrics[key] ?? false
                                                }
                                                onChange={(e) =>
                                                    setData('metrics', {
                                                        ...data.metrics,
                                                        [key]: e.target.checked,
                                                    })
                                                }
                                                className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                            />
                                            <span className="text-sm text-sp-muted">
                                                {label}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                        </div>

                        <div className="flex justify-end">
                            <PrimaryButton disabled={processing}>
                                {t('reports.generate_pdf')}
                            </PrimaryButton>
                        </div>
                    </form>
                )}
        </WorkspaceLayout>
    );
}
