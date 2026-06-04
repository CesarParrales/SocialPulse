import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { PageProps } from '@/types';

export default function Create({
    timezones,
    industryCategories,
    agencies,
}: PageProps<{
    timezones: string[];
    industryCategories: string[];
    agencies: Array<{ id: number; name: string }>;
}>) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        industry_category: '',
        timezone: timezones[0] ?? 'UTC',
        agency_id: agencies[0]?.id ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('workspaces.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Nuevo workspace
                </h2>
            }
        >
            <Head title="Nuevo workspace" />

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} className="space-y-6">
                            {agencies.length > 0 && (
                                <div>
                                    <InputLabel
                                        htmlFor="agency_id"
                                        value="Agencia"
                                    />
                                    <select
                                        id="agency_id"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.agency_id}
                                        onChange={(e) =>
                                            setData(
                                                'agency_id',
                                                Number(e.target.value),
                                            )
                                        }
                                    >
                                        {agencies.map((agency) => (
                                            <option
                                                key={agency.id}
                                                value={agency.id}
                                            >
                                                {agency.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.agency_id}
                                        className="mt-2"
                                    />
                                </div>
                            )}

                            <div>
                                <InputLabel htmlFor="name" value="Nombre" />
                                <TextInput
                                    id="name"
                                    className="mt-1 block w-full"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    required
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="industry_category"
                                    value="Industria"
                                />
                                <select
                                    id="industry_category"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={data.industry_category}
                                    onChange={(e) =>
                                        setData(
                                            'industry_category',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="">Seleccionar…</option>
                                    {industryCategories.map((category) => (
                                        <option
                                            key={category}
                                            value={category}
                                        >
                                            {category}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.industry_category}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="timezone"
                                    value="Zona horaria"
                                />
                                <select
                                    id="timezone"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={data.timezone}
                                    onChange={(e) =>
                                        setData('timezone', e.target.value)
                                    }
                                    required
                                >
                                    {timezones.map((tz) => (
                                        <option key={tz} value={tz}>
                                            {tz}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.timezone}
                                    className="mt-2"
                                />
                            </div>

                            <PrimaryButton disabled={processing}>
                                Crear workspace
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
