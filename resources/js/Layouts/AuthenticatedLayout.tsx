import ApplicationLogo from '@/Components/ApplicationLogo';
import AppTopBar from '@/Components/AppTopBar';
import SidebarNavLink from '@/Components/UI/SidebarNavLink';
import { useTranslation } from '@/lib/i18n';
import {
    readSidebarCollapsed,
    writeSidebarCollapsed,
} from '@/lib/sidebarStorage';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

function NavIcon({ d }: { d: string }) {
    return (
        <svg
            className="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={1.75}
        >
            <path strokeLinecap="round" strokeLinejoin="round" d={d} />
        </svg>
    );
}

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user!;
    const isClientReadonly = user.is_client_readonly === true;
    const { t } = useTranslation();
    const isSuperAdmin = user.roles.includes('super_admin');
    const hasAgency = user.agency_id != null;
    const canManageTeam =
        !isClientReadonly &&
        (user.roles.includes('agency_admin') || isSuperAdmin) &&
        hasAgency;
    const canAgencySettings =
        !isClientReadonly &&
        (user.roles.includes('agency_admin') || isSuperAdmin) &&
        hasAgency;

    const clientHomeUrl = usePage().props.clientHomeUrl;
    const homeHref =
        isClientReadonly && clientHomeUrl
            ? clientHomeUrl
            : route('dashboard');

    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(
        readSidebarCollapsed,
    );
    const closeSidebar = () => setSidebarOpen(false);

    const toggleSidebarCollapse = () => {
        setSidebarCollapsed((prev) => {
            const next = !prev;
            writeSidebarCollapsed(next);
            return next;
        });
    };

    const navLinkProps = {
        collapsed: sidebarCollapsed,
        onNavigate: closeSidebar,
    };

    return (
        <div className="min-h-screen bg-sp-surface lg:flex">
            {sidebarOpen && (
                <button
                    type="button"
                    className="fixed inset-0 z-40 bg-sp-ink/50 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                    aria-label={t('nav.close_menu')}
                />
            )}

            <aside
                aria-label={t('nav.main_navigation')}
                aria-expanded={!sidebarCollapsed}
                className={
                    'fixed inset-y-0 left-0 z-50 flex h-screen flex-col overflow-hidden bg-sp-sidebar transition-all duration-200 lg:translate-x-0 ' +
                    (sidebarCollapsed ? 'w-[4.5rem] ' : 'w-64 ') +
                    (sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0')
                }
            >
                <div className="flex h-14 items-center border-b border-white/10 px-5 lg:hidden">
                    <Link href={homeHref} onClick={closeSidebar}>
                        <ApplicationLogo showWordmark variant="light" />
                    </Link>
                </div>

                <nav
                    className={
                        'flex-1 space-y-1 overflow-y-auto py-4 ' +
                        (sidebarCollapsed ? 'px-2' : 'px-3')
                    }
                >
                    {isClientReadonly ? (
                        <>
                            <SidebarNavLink
                                href={homeHref}
                                active={route().current('workspaces.dashboard')}
                                {...navLinkProps}
                                icon={
                                    <NavIcon d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                }
                            >
                                {t('nav.dashboard')}
                            </SidebarNavLink>
                            <SidebarNavLink
                                href={route('workspaces.index')}
                                active={route().current('workspaces.index')}
                                {...navLinkProps}
                                icon={
                                    <NavIcon d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                }
                            >
                                {t('nav.workspaces')}
                            </SidebarNavLink>
                        </>
                    ) : (
                        <>
                            <SidebarNavLink
                                href={route('dashboard')}
                                active={route().current('dashboard')}
                                {...navLinkProps}
                                icon={
                                    <NavIcon d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                }
                            >
                                {t('nav.home')}
                            </SidebarNavLink>
                            <SidebarNavLink
                                href={route('workspaces.index')}
                                active={route().current('workspaces.*')}
                                {...navLinkProps}
                                icon={
                                    <NavIcon d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                }
                            >
                                {t('nav.workspaces')}
                            </SidebarNavLink>
                        </>
                    )}
                    {canManageTeam && (
                        <SidebarNavLink
                            href={route('team.index')}
                            active={route().current('team.*')}
                            {...navLinkProps}
                            icon={
                                <NavIcon d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            }
                        >
                            {t('nav.team')}
                        </SidebarNavLink>
                    )}
                    {canAgencySettings && (
                        <SidebarNavLink
                            href={route('settings.index')}
                            active={route().current('settings.*') ?? false}
                            {...navLinkProps}
                            icon={
                                <NavIcon d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            }
                        >
                            {t('nav.settings')}
                        </SidebarNavLink>
                    )}
                </nav>
            </aside>

            <div
                className={
                    'flex min-h-screen min-w-0 flex-1 flex-col transition-[margin] duration-200 ' +
                    (sidebarCollapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-64')
                }
            >
                <AppTopBar
                    homeHref={homeHref}
                    onOpenSidebar={() => setSidebarOpen(true)}
                    sidebarCollapsed={sidebarCollapsed}
                    onToggleSidebarCollapse={toggleSidebarCollapse}
                />

                {header && (
                    <div className="border-b border-sp-border bg-white">
                        <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {header}
                        </div>
                    </div>
                )}

                <main className="flex-1">{children}</main>
            </div>
        </div>
    );
}
