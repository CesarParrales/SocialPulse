import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { PageProps, Workspace } from '@/types';

export default function Show({
    workspace,
    canAssignMembers,
    assignableOperators,
    memberRoles,
}: PageProps<{
    workspace: Workspace;
    canAssignMembers: boolean;
    assignableOperators: Array<{ id: number; name: string; email: string }>;
    memberRoles: Array<{ value: string; label: string }>;
}>) {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        email: assignableOperators[0]?.email ?? '',
        role: memberRoles[0]?.value ?? 'operator',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('workspaces.members.store', workspace.id), {
            onSuccess: () => reset('email'),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {workspace.name}
                </h2>
            }
        >
            <Head title={workspace.name} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="rounded-md bg-green-50 p-4 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm text-gray-500">
                                    Agencia
                                </dt>
                                <dd className="font-medium text-gray-900">
                                    {workspace.agency?.name}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm text-gray-500">
                                    Zona horaria
                                </dt>
                                <dd className="font-medium text-gray-900">
                                    {workspace.timezone}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm text-gray-500">
                                    Industria
                                </dt>
                                <dd className="font-medium text-gray-900">
                                    {workspace.industry_category ?? '—'}
                                </dd>
                            </div>
                        </dl>
                        <div className="mt-6">
                            <a
                                href={route(
                                    'workspaces.connections.index',
                                    workspace.id,
                                )}
                                className="text-sm font-medium text-indigo-600 hover:underline"
                            >
                                Gestionar conexiones Meta / Google →
                            </a>
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-medium text-gray-900">
                            Miembros asignados
                        </h3>
                        {workspace.members && workspace.members.length > 0 ? (
                            <ul className="divide-y divide-gray-200">
                                {workspace.members.map((member) => (
                                    <li
                                        key={member.id}
                                        className="flex items-center justify-between py-3"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                {member.name}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {member.email}
                                            </p>
                                        </div>
                                        <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                            {member.pivot.role}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-sm text-gray-500">
                                Sin miembros asignados aún.
                            </p>
                        )}
                    </div>

                    {canAssignMembers && assignableOperators.length > 0 && (
                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">
                                Asignar operador
                            </h3>
                            <form onSubmit={submit} className="space-y-4">
                                <div>
                                    <InputLabel
                                        htmlFor="email"
                                        value="Operador"
                                    />
                                    <select
                                        id="email"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                    >
                                        {assignableOperators.map((operator) => (
                                            <option
                                                key={operator.id}
                                                value={operator.email}
                                            >
                                                {operator.name} ({operator.email})
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.email}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel htmlFor="role" value="Rol" />
                                    <select
                                        id="role"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.role}
                                        onChange={(e) =>
                                            setData('role', e.target.value)
                                        }
                                    >
                                        {memberRoles.map((role) => (
                                            <option
                                                key={role.value}
                                                value={role.value}
                                            >
                                                {role.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.role}
                                        className="mt-2"
                                    />
                                </div>

                                <PrimaryButton disabled={processing}>
                                    Asignar
                                </PrimaryButton>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
