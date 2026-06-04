import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link } from '@inertiajs/react';
import { PageProps, Workspace } from '@/types';

export default function Index({
    workspaces,
    canCreate,
}: PageProps<{
    workspaces: Workspace[];
    canCreate: boolean;
}>) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Workspaces
                    </h2>
                    {canCreate && (
                        <Link href={route('workspaces.create')}>
                            <PrimaryButton>Nuevo workspace</PrimaryButton>
                        </Link>
                    )}
                </div>
            }
        >
            <Head title="Workspaces" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {workspaces.length === 0 ? (
                            <div className="p-6 text-gray-600">
                                No hay workspaces disponibles.
                            </div>
                        ) : (
                            <ul className="divide-y divide-gray-200">
                                {workspaces.map((workspace) => (
                                    <li key={workspace.id}>
                                        <Link
                                            href={route(
                                                'workspaces.show',
                                                workspace.id,
                                            )}
                                            className="block px-6 py-4 transition hover:bg-gray-50"
                                        >
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="font-medium text-gray-900">
                                                        {workspace.name}
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        {workspace.agency
                                                            ?.name ?? '—'}{' '}
                                                        · {workspace.timezone}
                                                    </p>
                                                </div>
                                                {workspace.industry_category && (
                                                    <span className="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                                                        {
                                                            workspace.industry_category
                                                        }
                                                    </span>
                                                )}
                                            </div>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
