import { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/UI/PageHeader';
import WorkspaceNav, { WorkspaceNavActive } from '@/Components/UI/WorkspaceNav';
import AssetScopeBar, { AssetScopeConfig } from '@/Components/Dashboard/AssetScopeBar';

export default function WorkspaceLayout({
    headTitle,
    title,
    description,
    workspace,
    active,
    actions,
    assetScope,
    children,
}: {
    headTitle: string;
    title: string;
    description?: string;
    workspace: { id: number; name: string };
    active: WorkspaceNavActive;
    actions?: ReactNode;
    assetScope?: AssetScopeConfig;
    children: ReactNode;
}) {
    return (
        <AuthenticatedLayout>
            <Head title={headTitle} />

            <div className="mx-auto w-full min-w-0 max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={title}
                    description={description}
                    actions={actions}
                />
                <WorkspaceNav
                    workspaceId={workspace.id}
                    workspaceName={workspace.name}
                    active={active}
                />
                {assetScope && (
                    <div className="mt-4">
                        <AssetScopeBar
                            workspaceId={workspace.id}
                            assetScope={assetScope}
                        />
                    </div>
                )}
                {children}
            </div>
        </AuthenticatedLayout>
    );
}
