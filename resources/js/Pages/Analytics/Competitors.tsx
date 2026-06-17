import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import FlashAlert from '@/Components/UI/FlashAlert';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { AssetScopeConfig } from '@/Components/Dashboard/AssetScopeBar';
import { useTranslation } from '@/lib/i18n';
import { router, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { PageProps } from '@/types';

type CompetitorRow = {
    id: number;
    name: string;
    platform: string | null;
    handle: string | null;
    followers_count: number | null;
    avg_reach: number | null;
    avg_engagement_rate: number | null;
    notes: string | null;
    data_source_note: string | null;
    source: string;
};

type BenchmarkOverview = {
    client: {
        label: string;
        avg_reach: number | null;
        avg_engagement_rate: number | null;
        total_reach?: number | null;
        posts_count?: number | null;
        source: string;
    };
    comparison_rows: Array<{
        name: string;
        platform: string | null;
        followers_count: number | null;
        avg_reach: number | null;
        avg_engagement_rate: number | null;
        data_source_note: string | null;
        source: string;
    }>;
};

type InsightState = {
    prompt_text: string | null;
    ai_draft_text: string | null;
    reviewed_text: string | null;
    is_reviewed: boolean;
    prompt_generated_at: string | null;
    reviewed_at: string | null;
} | null;

function formatNumber(value: number | null | undefined): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return new Intl.NumberFormat('es').format(value);
}

function formatPercent(value: number | null | undefined): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return `${value.toFixed(2)}%`;
}

export default function Competitors({
    workspace,
    canManage,
    assetScope,
    competitors,
    benchmarkOverview,
    insight,
}: PageProps<{
    workspace: {
        id: number;
        name: string;
        industry_category: string | null;
        region: string | null;
    };
    canManage: boolean;
    assetScope: AssetScopeConfig;
    competitors: CompetitorRow[];
    benchmarkOverview: BenchmarkOverview;
    insight: InsightState;
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;
    const [copied, setCopied] = useState(false);

    const competitorForm = useForm({
        name: '',
        platform: '',
        handle: '',
        followers_count: '',
        avg_reach: '',
        avg_engagement_rate: '',
        data_source_note: '',
        notes: '',
    });

    const insightForm = useForm({
        ai_draft_text: insight?.ai_draft_text ?? '',
        reviewed_text: insight?.reviewed_text ?? '',
    });

    const submitCompetitor = (event: FormEvent) => {
        event.preventDefault();
        competitorForm.post(route('workspaces.competitors.store', workspace.id), {
            preserveScroll: true,
            onSuccess: () => competitorForm.reset(),
        });
    };

    const submitInsight = (event: FormEvent) => {
        event.preventDefault();
        insightForm.put(route('workspaces.competitors.insight', workspace.id), {
            preserveScroll: true,
        });
    };

    const generatePrompt = () => {
        router.post(
            route('workspaces.competitors.prompt', workspace.id),
            { asset_id: assetScope.selected_asset_id ?? undefined },
            { preserveScroll: true },
        );
    };

    const copyPrompt = async () => {
        if (!insight?.prompt_text) {
            return;
        }

        await navigator.clipboard.writeText(insight.prompt_text);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 2000);
    };

    return (
        <WorkspaceLayout
            headTitle={`${t('competitors.title')} — ${workspace.name}`}
            title={t('competitors.title')}
            description={t('competitors.description', { name: workspace.name })}
            workspace={workspace}
            active="competitors"
            assetScope={assetScope}
        >
            <div className="space-y-6">
                {flash.success && <FlashAlert message={flash.success} />}

                <CalloutBanner
                    title={t('competitors.manual_banner_title')}
                    variant="info"
                >
                    {t('competitors.manual_banner_body')}
                </CalloutBanner>

                <div className="sp-card p-6">
                    <h3 className="text-base font-semibold text-sp-ink">
                        {t('competitors.comparison_title')}
                    </h3>
                    <p className="mt-1 text-sm text-sp-muted">
                        {t('competitors.comparison_hint')}
                    </p>

                    <div className="mt-4 overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b border-sp-border text-left text-sp-muted">
                                    <th className="px-3 py-2">{t('common.name')}</th>
                                    <th className="px-3 py-2">{t('competitors.followers')}</th>
                                    <th className="px-3 py-2">{t('competitors.avg_reach')}</th>
                                    <th className="px-3 py-2">{t('competitors.engagement_rate')}</th>
                                    <th className="px-3 py-2">{t('competitors.source')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr className="border-b border-sp-border bg-sp-surface/60">
                                    <td className="px-3 py-2 font-medium text-sp-ink">
                                        {benchmarkOverview.client.label}
                                    </td>
                                    <td className="px-3 py-2">—</td>
                                    <td className="px-3 py-2">
                                        {formatNumber(benchmarkOverview.client.avg_reach)}
                                    </td>
                                    <td className="px-3 py-2">
                                        {formatPercent(benchmarkOverview.client.avg_engagement_rate)}
                                    </td>
                                    <td className="px-3 py-2 text-sp-muted">
                                        {t('competitors.source_ingested')}
                                    </td>
                                </tr>
                                {benchmarkOverview.comparison_rows.map((row) => (
                                    <tr
                                        key={row.name}
                                        className="border-b border-sp-border"
                                    >
                                        <td className="px-3 py-2 font-medium text-sp-ink">
                                            {row.name}
                                            {row.platform && (
                                                <span className="ml-2 text-xs text-sp-muted">
                                                    {row.platform}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-3 py-2">
                                            {formatNumber(row.followers_count)}
                                        </td>
                                        <td className="px-3 py-2">
                                            {formatNumber(row.avg_reach)}
                                        </td>
                                        <td className="px-3 py-2">
                                            {formatPercent(row.avg_engagement_rate)}
                                        </td>
                                        <td className="px-3 py-2 text-sp-muted">
                                            {row.data_source_note || t('competitors.source_manual')}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {canManage && (
                    <div className="sp-card p-6">
                        <h3 className="text-base font-semibold text-sp-ink">
                            {t('competitors.add_title')}
                        </h3>
                        <form
                            onSubmit={submitCompetitor}
                            className="mt-4 grid gap-4 sm:grid-cols-2"
                        >
                            <div>
                                <InputLabel htmlFor="name" value={t('common.name')} />
                                <TextInput
                                    id="name"
                                    className="mt-1 block w-full"
                                    value={competitorForm.data.name}
                                    onChange={(e) =>
                                        competitorForm.setData('name', e.target.value)
                                    }
                                    required
                                />
                                <InputError message={competitorForm.errors.name} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="platform" value={t('competitors.platform')} />
                                <select
                                    id="platform"
                                    className="sp-input mt-1 block w-full"
                                    value={competitorForm.data.platform}
                                    onChange={(e) =>
                                        competitorForm.setData('platform', e.target.value)
                                    }
                                >
                                    <option value="">{t('common.optional')}</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="other">{t('common.other')}</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel htmlFor="handle" value={t('competitors.handle')} />
                                <TextInput
                                    id="handle"
                                    className="mt-1 block w-full"
                                    value={competitorForm.data.handle}
                                    onChange={(e) =>
                                        competitorForm.setData('handle', e.target.value)
                                    }
                                />
                            </div>
                            <div>
                                <InputLabel htmlFor="followers_count" value={t('competitors.followers')} />
                                <TextInput
                                    id="followers_count"
                                    type="number"
                                    min={0}
                                    className="mt-1 block w-full"
                                    value={competitorForm.data.followers_count}
                                    onChange={(e) =>
                                        competitorForm.setData('followers_count', e.target.value)
                                    }
                                />
                            </div>
                            <div>
                                <InputLabel htmlFor="avg_reach" value={t('competitors.avg_reach')} />
                                <TextInput
                                    id="avg_reach"
                                    type="number"
                                    min={0}
                                    step="0.01"
                                    className="mt-1 block w-full"
                                    value={competitorForm.data.avg_reach}
                                    onChange={(e) =>
                                        competitorForm.setData('avg_reach', e.target.value)
                                    }
                                />
                            </div>
                            <div>
                                <InputLabel htmlFor="avg_engagement_rate" value={t('competitors.engagement_rate')} />
                                <TextInput
                                    id="avg_engagement_rate"
                                    type="number"
                                    min={0}
                                    max={100}
                                    step="0.01"
                                    className="mt-1 block w-full"
                                    value={competitorForm.data.avg_engagement_rate}
                                    onChange={(e) =>
                                        competitorForm.setData('avg_engagement_rate', e.target.value)
                                    }
                                />
                            </div>
                            <div className="sm:col-span-2">
                                <InputLabel htmlFor="data_source_note" value={t('competitors.data_source')} />
                                <TextInput
                                    id="data_source_note"
                                    className="mt-1 block w-full"
                                    value={competitorForm.data.data_source_note}
                                    onChange={(e) =>
                                        competitorForm.setData('data_source_note', e.target.value)
                                    }
                                    placeholder={t('competitors.data_source_placeholder')}
                                />
                            </div>
                            <div className="sm:col-span-2">
                                <InputLabel htmlFor="notes" value={t('competitors.notes')} />
                                <textarea
                                    id="notes"
                                    className="sp-input mt-1 block min-h-20 w-full"
                                    value={competitorForm.data.notes}
                                    onChange={(e) =>
                                        competitorForm.setData('notes', e.target.value)
                                    }
                                />
                            </div>
                            <div className="sm:col-span-2">
                                <PrimaryButton disabled={competitorForm.processing}>
                                    {t('competitors.add_button')}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                )}

                {competitors.length > 0 && (
                    <div className="sp-card p-6">
                        <h3 className="text-base font-semibold text-sp-ink">
                            {t('competitors.registered_title')}
                        </h3>
                        <ul className="mt-4 divide-y divide-sp-border">
                            {competitors.map((competitor) => (
                                <li
                                    key={competitor.id}
                                    className="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <p className="font-medium text-sp-ink">
                                            {competitor.name}
                                        </p>
                                        <p className="text-xs text-sp-muted">
                                            {[
                                                competitor.platform,
                                                competitor.handle,
                                                competitor.data_source_note,
                                            ]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        </p>
                                    </div>
                                    {canManage && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.delete(
                                                    route('workspaces.competitors.destroy', [
                                                        workspace.id,
                                                        competitor.id,
                                                    ]),
                                                )
                                            }
                                            className="text-sm text-red-600 hover:underline"
                                        >
                                            {t('common.delete')}
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {canManage && (
                    <div className="sp-card p-6">
                        <h3 className="text-base font-semibold text-sp-ink">
                            {t('competitors.ai_title')}
                        </h3>
                        <p className="mt-1 text-sm text-sp-muted">
                            {t('competitors.ai_hint')}
                        </p>

                        <div className="mt-4 flex flex-wrap gap-3">
                            <PrimaryButton type="button" onClick={generatePrompt}>
                                {t('competitors.generate_prompt')}
                            </PrimaryButton>
                            {insight?.prompt_text && (
                                <SecondaryButton type="button" onClick={copyPrompt}>
                                    {copied
                                        ? t('competitors.prompt_copied')
                                        : t('competitors.copy_prompt')}
                                </SecondaryButton>
                            )}
                        </div>

                        {insight?.prompt_text && (
                            <textarea
                                readOnly
                                className="sp-input mt-4 min-h-48 w-full font-mono text-xs"
                                value={insight.prompt_text}
                            />
                        )}

                        <form onSubmit={submitInsight} className="mt-6 space-y-4">
                            <div>
                                <InputLabel
                                    htmlFor="ai_draft_text"
                                    value={t('competitors.ai_draft')}
                                />
                                <textarea
                                    id="ai_draft_text"
                                    className="sp-input mt-1 min-h-32 w-full"
                                    value={insightForm.data.ai_draft_text}
                                    onChange={(e) =>
                                        insightForm.setData('ai_draft_text', e.target.value)
                                    }
                                    placeholder={t('competitors.ai_draft_placeholder')}
                                />
                            </div>
                            <div>
                                <InputLabel
                                    htmlFor="reviewed_text"
                                    value={t('competitors.reviewed_text')}
                                />
                                <textarea
                                    id="reviewed_text"
                                    className="sp-input mt-1 min-h-32 w-full"
                                    value={insightForm.data.reviewed_text}
                                    onChange={(e) =>
                                        insightForm.setData('reviewed_text', e.target.value)
                                    }
                                    placeholder={t('competitors.reviewed_placeholder')}
                                />
                            </div>
                            <PrimaryButton disabled={insightForm.processing}>
                                {t('competitors.save_insight')}
                            </PrimaryButton>
                        </form>
                    </div>
                )}
            </div>
        </WorkspaceLayout>
    );
}
