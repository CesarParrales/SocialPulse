import PublicDashboardSharePanel from '@/Components/Settings/PublicDashboardSharePanel';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import Card from '@/Components/UI/Card';
import FlashAlert from '@/Components/UI/FlashAlert';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { useTranslation } from '@/lib/i18n';
import { useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import { PageProps } from '@/types';

export default function Workspace({
    workspace,
    timezones,
    industryCategories,
    regions,
    publicDashboard,
}: PageProps<{
    workspace: {
        id: number;
        name: string;
        industry_category: string | null;
        region: string | null;
        timezone: string;
    };
    publicDashboard: {
        enabled: boolean;
        url: string | null;
        enabled_at: string | null;
    };
    timezones: string[];
    industryCategories: string[];
    regions: string[];
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    const { data, setData, put, processing, errors } = useForm({
        name: workspace.name,
        industry_category: workspace.industry_category ?? '',
        region: workspace.region ?? '',
        timezone: workspace.timezone,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(route('settings.workspace.update', workspace.id));
    };

    const hasSegmentation =
        data.industry_category !== '' || data.region !== '';

    return (
        <WorkspaceLayout
            headTitle={t('workspace_settings.title')}
            title={t('workspace_settings.title')}
            description={t('workspace_settings.description')}
            workspace={workspace}
            active="settings"
        >
                {flash.success && (
                    <FlashAlert message={flash.success} className="mb-6" />
                )}

                <Card>
                    <form onSubmit={submit} className="space-y-8">
                        <section className="space-y-4">
                            <h3 className="text-sm font-semibold text-sp-ink">
                                {t('workspace_settings.general')}
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
                                    {t('workspace_settings.timezone_hint')}
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
                                    {t('workspace_settings.segmentation')}
                                </h3>
                                <p className="mt-1 text-xs text-sp-muted">
                                    {t('workspace_settings.segmentation_hint')}
                                </p>
                            </div>

                            {!hasSegmentation && (
                                <CalloutBanner
                                    title={t(
                                        'workspace_settings.segmentation_tip_title',
                                    )}
                                    variant="info"
                                >
                                    {t(
                                        'workspace_settings.segmentation_tip',
                                    )}
                                </CalloutBanner>
                            )}

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

                        <PublicDashboardSharePanel
                            workspaceId={workspace.id}
                            enabled={publicDashboard.enabled}
                            url={publicDashboard.url}
                            enabledAt={publicDashboard.enabled_at}
                        />

                        <div className="flex justify-end border-t border-sp-border pt-6">
                            <PrimaryButton disabled={processing}>
                                {t('common.save')}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
        </WorkspaceLayout>
    );
}
