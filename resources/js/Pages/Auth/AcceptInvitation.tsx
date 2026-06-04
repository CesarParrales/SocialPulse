import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function AcceptInvitation({
    email,
    agencyName,
    role,
    token,
}: {
    email: string;
    agencyName: string;
    role: string;
    token: string;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('invitations.store', token), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Aceptar invitación" />

            <div className="mb-4 text-sm text-gray-600">
                Te unes a <strong>{agencyName}</strong> como{' '}
                <strong>{role.replace('_', ' ')}</strong>.
            </div>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Correo" />
                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full bg-gray-100"
                        value={email}
                        disabled
                    />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="name" value="Nombre" />
                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        autoFocus
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Contraseña" />
                    <TextInput
                        id="password"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirmar contraseña"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />
                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-6 flex items-center justify-end">
                    <PrimaryButton disabled={processing}>
                        Crear cuenta
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
