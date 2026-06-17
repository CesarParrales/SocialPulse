import { Link } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';

export type OnboardingStep = {
    key: string;
    label: string;
    description?: string;
    done: boolean;
    href?: string;
};

export default function OnboardingChecklist({
    title,
    completeMessage,
    steps,
}: {
    title: string;
    completeMessage?: string;
    steps: OnboardingStep[];
}) {
    const { t } = useTranslation();
    const completedCount = steps.filter((step) => step.done).length;
    const allDone = completedCount === steps.length;

    return (
        <div className="sp-card p-6">
            <div className="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 className="text-base font-semibold text-sp-ink">
                        {title}
                    </h3>
                    <p className="mt-1 text-sm text-sp-muted">
                        {completedCount}/{steps.length}
                    </p>
                </div>
                <div className="h-2 w-24 overflow-hidden rounded-full bg-sp-surface">
                    <div
                        className="h-full rounded-full bg-sp-primary transition-all duration-300"
                        style={{
                            width: `${(completedCount / steps.length) * 100}%`,
                        }}
                    />
                </div>
            </div>

            {allDone && completeMessage ? (
                <p className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {completeMessage}
                </p>
            ) : (
                <ol className="space-y-3">
                    {steps.map((step, index) => (
                        <li
                            key={step.key}
                            className="flex gap-3 rounded-lg border border-sp-border p-3"
                        >
                            <span
                                className={
                                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold ' +
                                    (step.done
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-sp-surface text-sp-muted')
                                }
                                aria-hidden
                            >
                                {step.done ? '✓' : index + 1}
                            </span>
                            <div className="min-w-0 flex-1">
                                <p
                                    className={
                                        'text-sm font-medium ' +
                                        (step.done
                                            ? 'text-sp-muted line-through'
                                            : 'text-sp-ink')
                                    }
                                >
                                    {step.label}
                                </p>
                                {step.description && (
                                    <p className="mt-0.5 text-xs text-sp-muted">
                                        {step.description}
                                    </p>
                                )}
                                {!step.done && step.href && (
                                    <Link
                                        href={step.href}
                                        className="sp-link mt-2 inline-block text-xs"
                                    >
                                        {t('onboarding.go')}
                                    </Link>
                                )}
                            </div>
                        </li>
                    ))}
                </ol>
            )}
        </div>
    );
}
