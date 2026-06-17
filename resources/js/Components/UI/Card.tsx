import { PropsWithChildren, ReactNode } from 'react';

export default function Card({
    children,
    className = '',
    padding = 'p-6',
}: PropsWithChildren<{ className?: string; padding?: string }>) {
    return (
        <div className={`sp-card ${padding} ${className}`}>{children}</div>
    );
}

export function CardTitle({
    children,
    action,
}: {
    children: ReactNode;
    action?: ReactNode;
}) {
    return (
        <div className="mb-4 flex items-center justify-between gap-4">
            <h3 className="text-base font-semibold text-sp-ink">{children}</h3>
            {action}
        </div>
    );
}
