import { useTranslation } from '@/lib/i18n';

export type IntegrationPlatformTab =
    | 'overview'
    | 'meta'
    | 'google'
    | 'tiktok'
    | 'linkedin'
    | 'youtube';

type TabConfig = {
    key: IntegrationPlatformTab;
    label: string;
    configured?: boolean;
};

export default function IntegrationsInternalTabs({
    activeTab,
    onChange,
    tabs,
}: {
    activeTab: IntegrationPlatformTab;
    onChange: (tab: IntegrationPlatformTab) => void;
    tabs: TabConfig[];
}) {
    const { t } = useTranslation();

    return (
        <nav
            aria-label={t('settings.integrations_platform_nav')}
            className="mb-6 flex gap-1 overflow-x-auto border-b border-sp-border pb-px"
        >
            {tabs.map((tab) => (
                <button
                    key={tab.key}
                    type="button"
                    onClick={() => onChange(tab.key)}
                    aria-current={activeTab === tab.key ? 'page' : undefined}
                    className={
                        'flex shrink-0 items-center gap-2 border-b-2 px-3 py-2.5 text-sm font-medium transition-colors ' +
                        (activeTab === tab.key
                            ? 'border-sp-primary text-sp-primary'
                            : 'border-transparent text-sp-muted hover:border-sp-border hover:text-sp-ink')
                    }
                >
                    {tab.key !== 'overview' && tab.configured !== undefined && (
                        <span
                            className={
                                'h-2 w-2 shrink-0 rounded-full ' +
                                (tab.configured
                                    ? 'bg-emerald-500'
                                    : 'bg-sp-border')
                            }
                            aria-hidden="true"
                        />
                    )}
                    {tab.label}
                </button>
            ))}
        </nav>
    );
}

export type IntegrationPlatformSection = Exclude<
    IntegrationPlatformTab,
    'overview'
>;
