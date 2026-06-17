import { InertiaLinkProps, Link } from '@inertiajs/react';
import { ReactNode } from 'react';

export default function SidebarNavLink({
    active = false,
    icon,
    badge,
    badgePulse = false,
    collapsed = false,
    className = '',
    onNavigate,
    children,
    ...props
}: InertiaLinkProps & {
    active: boolean;
    icon?: ReactNode;
    badge?: number;
    badgePulse?: boolean;
    collapsed?: boolean;
    onNavigate?: () => void;
}) {
    const label = typeof children === 'string' ? children : undefined;

    return (
        <Link
            {...props}
            aria-current={active ? 'page' : undefined}
            title={collapsed ? label : undefined}
            onClick={(event) => {
                props.onClick?.(event);
                onNavigate?.();
            }}
            className={
                'relative flex items-center rounded-lg py-2.5 text-sm font-medium transition-all duration-150 ' +
                (collapsed
                    ? 'justify-center px-2 '
                    : 'gap-3 px-3 ') +
                (active
                    ? 'bg-sp-primary/20 text-white shadow-sm ring-1 ring-white/10'
                    : 'text-slate-400 hover:bg-sp-sidebar-hover hover:text-white') +
                ` ${className}`
            }
        >
            {icon && (
                <span
                    className={
                        'shrink-0 ' + (active ? 'opacity-100' : 'opacity-80')
                    }
                >
                    {icon}
                </span>
            )}
            {!collapsed && (
                <span className="min-w-0 flex-1 truncate">{children}</span>
            )}
            {!collapsed && badge !== undefined && badge > 0 && (
                <span
                    className={
                        'ml-auto flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-sp-primary px-1.5 text-xs font-semibold text-white' +
                        (badgePulse ? ' animate-pulse' : '')
                    }
                    aria-label={String(badge)}
                >
                    {badge > 99 ? '99+' : badge}
                </span>
            )}
            {collapsed && badge !== undefined && badge > 0 && (
                <span
                    className={
                        'absolute right-1 top-1 flex h-2 w-2 rounded-full bg-sp-primary' +
                        (badgePulse ? ' animate-pulse' : '')
                    }
                    aria-label={String(badge)}
                />
            )}
        </Link>
    );
}
