import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { PageProps } from '@/types';

interface TeamMember {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

interface PendingInvitation {
    id: number;
    email: string;
    role: string;
    expires_at: string;
}

export default function Index({
    agency,
    members,
    invitations,
    invitableRoles,
}: PageProps<{
    agency: { id: number; name: string };
    members: TeamMember[];
    invitations: PendingInvitation[];
    invitableRoles: Array<{ value: string; label: string }>;
}>) {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: invitableRoles[1]?.value ?? 'operator',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('team.invitations.store'), {
            onSuccess: () => reset('email'),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Equipo — {agency.name}
                </h2>
            }
        >
            <Head title="Equipo" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="rounded-md bg-green-50 p-4 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-medium text-gray-900">
                            Miembros activos
                        </h3>
                        <ul className="divide-y divide-gray-200">
                            {members.map((member) => (
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
                                    <span className="text-xs uppercase tracking-wide text-gray-500">
                                        {member.roles.join(', ')}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-medium text-gray-900">
                            Invitar por correo
                        </h3>
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel htmlFor="email" value="Correo" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    required
                                />
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
                                    {invitableRoles.map((role) => (
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
                                Enviar invitación
                            </PrimaryButton>
                        </form>
                    </div>

                    {invitations.length > 0 && (
                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">
                                Invitaciones pendientes
                            </h3>
                            <ul className="divide-y divide-gray-200">
                                {invitations.map((invitation) => (
                                    <li
                                        key={invitation.id}
                                        className="flex items-center justify-between py-3"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                {invitation.email}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {invitation.role} · expira{' '}
                                                {new Date(
                                                    invitation.expires_at,
                                                ).toLocaleDateString('es')}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.delete(
                                                    route(
                                                        'team.invitations.destroy',
                                                        invitation.id,
                                                    ),
                                                )
                                            }
                                            className="text-sm text-red-600 hover:underline"
                                        >
                                            Cancelar
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
