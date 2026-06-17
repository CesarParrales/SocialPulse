const variants: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800',
    expired: 'bg-red-100 text-red-800',
    error: 'bg-red-100 text-red-800',
    pending: 'bg-amber-100 text-amber-800',
    ready: 'bg-emerald-100 text-emerald-800',
    processing: 'bg-blue-100 text-blue-800',
    generating: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800',
    draft: 'bg-sp-surface text-sp-muted',
    pending_review: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-red-100 text-red-800',
    published: 'bg-indigo-100 text-indigo-800',
    cancelled: 'bg-sp-surface text-sp-muted',
};

export default function StatusBadge({
    status,
    label,
}: {
    status: string;
    label?: string;
}) {
    const key = status.toLowerCase();
    const className = variants[key] ?? 'bg-sp-surface text-sp-muted';

    return (
        <span
            className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${className}`}
        >
            {label ?? status.replace(/_/g, ' ')}
        </span>
    );
}
