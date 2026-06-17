import { useTranslation } from '@/lib/i18n';
import { Link } from '@inertiajs/react';

type Tab = {
    key: string;
    label: string;
    href: string;
    active: boolean;
};

export default function SettingsSubNav({ tabs }: { tabs: Tab[] }) {
    const { t } = useTranslation();

    return (
        <nav
            aria-label={t('settings.subnav_label')}
            className="mb-8 flex flex-wrap gap-2 border-b border-sp-border pb-4"
        >
            {tabs.map((tab) => (
                <Link
                    key={tab.key}
                    href={tab.href}
                    className={
                        'rounded-lg px-3 py-2 text-sm font-medium transition-colors ' +
                        (tab.active
                            ? 'bg-sp-primary text-white'
                            : 'text-sp-muted hover:bg-sp-surface hover:text-sp-ink')
                    }
                    aria-current={tab.active ? 'page' : undefined}
                >
                    {tab.label}
                </Link>
            ))}
        </nav>
    );
}
