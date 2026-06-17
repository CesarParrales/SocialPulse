import { ReactNode } from 'react';

export default function PageHeader({
    title,
    description,
    actions,
    className = '',
}: {
    title: string;
    description?: string;
    actions?: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={
                'mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between ' +
                className
            }
        >
            <div className="min-w-0">
                <h1 className="text-2xl font-bold tracking-tight text-sp-ink">
                    {title}
                </h1>
                {description && (
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-sp-muted">
                        {description}
                    </p>
                )}
            </div>
            {actions && (
                <div className="flex shrink-0 flex-wrap items-center gap-3">
                    {actions}
                </div>
            )}
        </div>
    );
}
