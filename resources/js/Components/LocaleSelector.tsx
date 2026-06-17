import Dropdown from '@/Components/Dropdown';
import { useTranslation } from '@/lib/i18n';
import { PageProps } from '@/types';
import { router, usePage } from '@inertiajs/react';

function GlobeIcon({ className = 'h-4 w-4' }: { className?: string }) {
    return (
        <svg
            className={className}
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={1.75}
            aria-hidden="true"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
            />
        </svg>
    );
}

function ChevronIcon() {
    return (
        <svg
            className="h-4 w-4 shrink-0 text-sp-muted"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={2}
            aria-hidden="true"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M19 9l-7 7-7-7"
            />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg
            className="h-4 w-4 shrink-0 text-sp-primary"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={2}
            aria-hidden="true"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M5 13l4 4L19 7"
            />
        </svg>
    );
}

function LocaleMenu({
    align = 'right',
    triggerClassName,
}: {
    align?: 'left' | 'right';
    triggerClassName: string;
}) {
    const { t } = useTranslation();
    const { locale, localeOptions } = usePage<PageProps>().props;

    const currentLabel =
        localeOptions.find((option) => option.value === locale)?.label ??
        locale;

    const handleSelect = (value: string) => {
        if (value === locale) {
            return;
        }

        router.patch(route('locale.update'), { locale: value });
    };

    return (
        <Dropdown>
            <Dropdown.Trigger>
                <button
                    type="button"
                    className={triggerClassName}
                    aria-label={`${t('locale.label')}: ${currentLabel}`}
                    aria-haspopup="listbox"
                >
                    <GlobeIcon />
                    <span className="truncate">{currentLabel}</span>
                    <ChevronIcon />
                </button>
            </Dropdown.Trigger>
            <Dropdown.Content
                align={align}
                contentClasses="overflow-hidden rounded-lg border border-sp-border bg-white py-1 shadow-lg ring-0"
            >
                <ul role="listbox" aria-label={t('locale.label')}>
                    {localeOptions.map((option) => {
                        const selected = option.value === locale;

                        return (
                            <li key={option.value} role="none">
                                <button
                                    type="button"
                                    role="option"
                                    aria-selected={selected}
                                    onClick={() => handleSelect(option.value)}
                                    className={
                                        'flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left text-sm transition-colors ' +
                                        (selected
                                            ? 'bg-sp-primary/10 font-medium text-sp-primary'
                                            : 'text-sp-ink hover:bg-sp-surface')
                                    }
                                >
                                    <span>{option.label}</span>
                                    {selected && <CheckIcon />}
                                </button>
                            </li>
                        );
                    })}
                </ul>
            </Dropdown.Content>
        </Dropdown>
    );
}

export default function LocaleSelector({
    variant = 'sidebar',
}: {
    variant?: 'sidebar' | 'guest' | 'header';
}) {
    const { t } = useTranslation();
    const { locale, localeOptions } = usePage<PageProps>().props;

    const handleChange = (value: string) => {
        router.patch(route('locale.update'), { locale: value });
    };

    if (variant === 'header') {
        return (
            <LocaleMenu
                align="right"
                triggerClassName="flex min-w-[8.75rem] items-center gap-2 rounded-lg border border-sp-border bg-white px-2.5 py-2 text-sm text-sp-ink shadow-sm transition-colors hover:bg-sp-surface focus:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary/20"
            />
        );
    }

    if (variant === 'guest') {
        return (
            <div>
                <p className="mb-2 text-xs font-medium text-sp-muted">
                    {t('locale.label')}
                </p>
                <LocaleMenu
                    align="left"
                    triggerClassName="flex w-full items-center gap-2 rounded-lg border border-sp-border bg-white px-3 py-2.5 text-sm text-sp-ink shadow-sm transition-colors hover:bg-sp-surface focus:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary/20"
                />
            </div>
        );
    }

    const labelClass = 'block px-3 text-xs font-medium text-slate-400';

    return (
        <div className="space-y-2">
            <label className={labelClass} htmlFor="locale-select-sidebar">
                {t('locale.label')}
            </label>
            <div className="relative mx-3">
                <span className="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-slate-400">
                    <GlobeIcon className="h-4 w-4" />
                </span>
                <select
                    id="locale-select-sidebar"
                    value={locale}
                    onChange={(e) => handleChange(e.target.value)}
                    className="w-full appearance-none rounded-lg border-0 bg-sp-sidebar-hover py-2 pl-9 pr-8 text-sm text-white focus:ring-sp-primary"
                    aria-label={t('locale.label')}
                >
                    {localeOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <ChevronIcon />
                </span>
            </div>
        </div>
    );
}
