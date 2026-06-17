import { Link } from '@inertiajs/react';

export default function EmptyState({
    title,
    description,
    action,
}: {
    title: string;
    description?: string;
    action?: { label: string; href: string };
}) {
    return (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-sp-border bg-sp-surface/50 px-6 py-12 text-center">
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-sp-primary/10 text-sp-primary">
                <svg
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    strokeWidth={1.5}
                    aria-hidden
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    />
                </svg>
            </div>
            <h3 className="mt-4 text-base font-semibold text-sp-ink">{title}</h3>
            {description && (
                <p className="mt-2 max-w-sm text-sm text-sp-muted">{description}</p>
            )}
            {action && (
                <Link
                    href={action.href}
                    className="mt-6 inline-flex items-center rounded-lg bg-sp-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-sp-primary/90"
                >
                    {action.label}
                </Link>
            )}
        </div>
    );
}
