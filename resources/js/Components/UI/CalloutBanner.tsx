import { ReactNode } from 'react';

export default function CalloutBanner({
    title,
    children,
    variant = 'info',
}: {
    title: string;
    children: ReactNode;
    variant?: 'info' | 'warning';
}) {
    const styles =
        variant === 'warning'
            ? 'border-amber-200 bg-amber-50 text-amber-950'
            : 'border-violet-200 bg-violet-50 text-violet-950';

    return (
        <div className={`rounded-lg border p-4 ${styles}`}>
            <p className="text-sm font-semibold">{title}</p>
            <div className="mt-1 text-sm opacity-90">{children}</div>
        </div>
    );
}
