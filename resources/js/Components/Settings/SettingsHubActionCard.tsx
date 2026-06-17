import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';

export default function SettingsHubActionCard({
    href,
    title,
    description,
    icon,
    meta,
    emphasis = false,
}: {
    href: string;
    title: string;
    description: string;
    icon: ReactNode;
    meta?: ReactNode;
    emphasis?: boolean;
}) {
    return (
        <Link
            href={href}
            className={
                'group sp-card flex h-full flex-col p-5 transition-shadow hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary focus-visible:ring-offset-2 ' +
                (emphasis ? 'ring-1 ring-sp-primary/20' : '')
            }
        >
            <div className="flex items-start gap-4">
                <span
                    className={
                        'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ' +
                        (emphasis
                            ? 'bg-sp-primary/10 text-sp-primary'
                            : 'bg-sp-surface text-sp-muted group-hover:text-sp-primary')
                    }
                >
                    {icon}
                </span>
                <div className="min-w-0 flex-1">
                    <h2 className="text-base font-semibold text-sp-ink group-hover:text-sp-primary">
                        {title}
                    </h2>
                    <p className="mt-1 text-sm text-sp-muted">{description}</p>
                    {meta && <div className="mt-3">{meta}</div>}
                </div>
                <span
                    className="mt-1 shrink-0 text-sp-muted transition-transform group-hover:translate-x-0.5 group-hover:text-sp-primary"
                    aria-hidden="true"
                >
                    →
                </span>
            </div>
        </Link>
    );
}
