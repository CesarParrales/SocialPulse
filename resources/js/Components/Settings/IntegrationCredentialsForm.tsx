import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { IntegrationPlatformSection } from '@/Components/Settings/IntegrationsInternalTabs';
import { useTranslation } from '@/lib/i18n';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export type IntegrationCredentialsPayload = {
    meta_app_id: string;
    meta_api_version: string;
    meta_system_user_id: string;
    meta_business_id: string;
    google_client_id: string;
    tiktok_client_key: string;
    linkedin_client_id: string;
    youtube_client_id: string;
    has_meta_app_secret: boolean;
    has_meta_system_user_access_token: boolean;
    has_google_client_secret: boolean;
    has_google_developer_token: boolean;
    has_tiktok_client_secret: boolean;
    has_linkedin_client_secret: boolean;
    has_youtube_client_secret: boolean;
};

export default function IntegrationCredentialsForm({
    credentials,
    submitRoute,
    activeSection,
}: {
    credentials: IntegrationCredentialsPayload;
    submitRoute: string;
    activeSection?: IntegrationPlatformSection;
}) {
    const { t } = useTranslation();

    const { data, setData, put, processing, errors } = useForm({
        meta_app_id: credentials.meta_app_id,
        meta_app_secret: '',
        meta_api_version: credentials.meta_api_version,
        meta_system_user_id: credentials.meta_system_user_id,
        meta_system_user_access_token: '',
        meta_business_id: credentials.meta_business_id,
        google_client_id: credentials.google_client_id,
        google_client_secret: '',
        google_developer_token: '',
        tiktok_client_key: credentials.tiktok_client_key,
        tiktok_client_secret: '',
        linkedin_client_id: credentials.linkedin_client_id,
        linkedin_client_secret: '',
        youtube_client_id: credentials.youtube_client_id,
        youtube_client_secret: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(submitRoute);
    };

    const secretPlaceholder = (hasValue: boolean) =>
        hasValue ? t('settings.secret_keep_placeholder') : '';

    const showMeta = !activeSection || activeSection === 'meta';
    const showGoogle = !activeSection || activeSection === 'google';
    const showTiktok = !activeSection || activeSection === 'tiktok';
    const showLinkedin = !activeSection || activeSection === 'linkedin';
    const showYoutube = !activeSection || activeSection === 'youtube';

    return (
        <form onSubmit={submit} className="space-y-6">
            {showMeta && (
                <>
                    <fieldset className="space-y-4">
                        <legend className="text-sm font-semibold text-sp-ink">
                            {t('settings.meta')}
                        </legend>

                        <div>
                            <InputLabel
                                htmlFor="meta_app_id"
                                value={t('settings.meta_app_id')}
                            />
                            <TextInput
                                id="meta_app_id"
                                className="mt-1 block w-full"
                                value={data.meta_app_id}
                                onChange={(e) =>
                                    setData('meta_app_id', e.target.value)
                                }
                            />
                            <InputError
                                message={errors.meta_app_id}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="meta_app_secret"
                                value={t('settings.meta_app_secret')}
                            />
                            <TextInput
                                id="meta_app_secret"
                                type="password"
                                className="mt-1 block w-full"
                                value={data.meta_app_secret}
                                placeholder={secretPlaceholder(
                                    credentials.has_meta_app_secret,
                                )}
                                onChange={(e) =>
                                    setData('meta_app_secret', e.target.value)
                                }
                                autoComplete="off"
                            />
                            <InputError
                                message={errors.meta_app_secret}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="meta_api_version"
                                value={t('settings.meta_api_version')}
                            />
                            <TextInput
                                id="meta_api_version"
                                className="mt-1 block w-full"
                                value={data.meta_api_version}
                                placeholder="v22.0"
                                onChange={(e) =>
                                    setData('meta_api_version', e.target.value)
                                }
                            />
                            <InputError
                                message={errors.meta_api_version}
                                className="mt-2"
                            />
                        </div>
                    </fieldset>

                    <fieldset className="space-y-4 border-t border-sp-border pt-6">
                        <legend className="text-sm font-semibold text-sp-ink">
                            {t('settings.meta_system_user')}
                        </legend>

                        <div>
                            <InputLabel
                                htmlFor="meta_system_user_id"
                                value={t('settings.meta_system_user_id')}
                            />
                            <TextInput
                                id="meta_system_user_id"
                                className="mt-1 block w-full"
                                value={data.meta_system_user_id}
                                onChange={(e) =>
                                    setData(
                                        'meta_system_user_id',
                                        e.target.value,
                                    )
                                }
                            />
                            <InputError
                                message={errors.meta_system_user_id}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="meta_system_user_access_token"
                                value={t('settings.meta_system_user_token')}
                            />
                            <TextInput
                                id="meta_system_user_access_token"
                                type="password"
                                className="mt-1 block w-full"
                                value={data.meta_system_user_access_token}
                                placeholder={secretPlaceholder(
                                    credentials.has_meta_system_user_access_token,
                                )}
                                onChange={(e) =>
                                    setData(
                                        'meta_system_user_access_token',
                                        e.target.value,
                                    )
                                }
                                autoComplete="off"
                            />
                            <InputError
                                message={errors.meta_system_user_access_token}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="meta_business_id"
                                value={t('settings.meta_business_id')}
                            />
                            <TextInput
                                id="meta_business_id"
                                className="mt-1 block w-full"
                                value={data.meta_business_id}
                                onChange={(e) =>
                                    setData('meta_business_id', e.target.value)
                                }
                            />
                            <InputError
                                message={errors.meta_business_id}
                                className="mt-2"
                            />
                        </div>
                    </fieldset>
                </>
            )}

            {showGoogle && (
                <fieldset
                    className={
                        'space-y-4 ' +
                        (showMeta ? 'border-t border-sp-border pt-6' : '')
                    }
                >
                    <legend className="text-sm font-semibold text-sp-ink">
                        {t('settings.google')}
                    </legend>

                    <div>
                        <InputLabel
                            htmlFor="google_client_id"
                            value={t('settings.google_client_id')}
                        />
                        <TextInput
                            id="google_client_id"
                            className="mt-1 block w-full"
                            value={data.google_client_id}
                            onChange={(e) =>
                                setData('google_client_id', e.target.value)
                            }
                        />
                        <InputError
                            message={errors.google_client_id}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="google_client_secret"
                            value={t('settings.google_client_secret')}
                        />
                        <TextInput
                            id="google_client_secret"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.google_client_secret}
                            placeholder={secretPlaceholder(
                                credentials.has_google_client_secret,
                            )}
                            onChange={(e) =>
                                setData('google_client_secret', e.target.value)
                            }
                            autoComplete="off"
                        />
                        <InputError
                            message={errors.google_client_secret}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="google_developer_token"
                            value={t('settings.google_developer_token')}
                        />
                        <TextInput
                            id="google_developer_token"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.google_developer_token}
                            placeholder={secretPlaceholder(
                                credentials.has_google_developer_token,
                            )}
                            onChange={(e) =>
                                setData('google_developer_token', e.target.value)
                            }
                            autoComplete="off"
                        />
                        <InputError
                            message={errors.google_developer_token}
                            className="mt-2"
                        />
                    </div>
                </fieldset>
            )}

            {showTiktok && (
                <fieldset
                    className={
                        'space-y-4 ' +
                        (showMeta || showGoogle
                            ? 'border-t border-sp-border pt-6'
                            : '')
                    }
                >
                    <legend className="text-sm font-semibold text-sp-ink">
                        {t('settings.tiktok')}
                    </legend>

                    <div>
                        <InputLabel
                            htmlFor="tiktok_client_key"
                            value={t('settings.tiktok_client_key')}
                        />
                        <TextInput
                            id="tiktok_client_key"
                            className="mt-1 block w-full"
                            value={data.tiktok_client_key}
                            onChange={(e) =>
                                setData('tiktok_client_key', e.target.value)
                            }
                        />
                        <InputError
                            message={errors.tiktok_client_key}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="tiktok_client_secret"
                            value={t('settings.tiktok_client_secret')}
                        />
                        <TextInput
                            id="tiktok_client_secret"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.tiktok_client_secret}
                            placeholder={secretPlaceholder(
                                credentials.has_tiktok_client_secret,
                            )}
                            onChange={(e) =>
                                setData('tiktok_client_secret', e.target.value)
                            }
                            autoComplete="off"
                        />
                        <InputError
                            message={errors.tiktok_client_secret}
                            className="mt-2"
                        />
                    </div>
                </fieldset>
            )}

            {showLinkedin && (
                <fieldset
                    className={
                        'space-y-4 ' +
                        (showMeta || showGoogle || showTiktok
                            ? 'border-t border-sp-border pt-6'
                            : '')
                    }
                >
                    <legend className="text-sm font-semibold text-sp-ink">
                        {t('settings.linkedin')}
                    </legend>

                    <div>
                        <InputLabel
                            htmlFor="linkedin_client_id"
                            value={t('settings.linkedin_client_id')}
                        />
                        <TextInput
                            id="linkedin_client_id"
                            className="mt-1 block w-full"
                            value={data.linkedin_client_id}
                            onChange={(e) =>
                                setData('linkedin_client_id', e.target.value)
                            }
                        />
                        <InputError
                            message={errors.linkedin_client_id}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="linkedin_client_secret"
                            value={t('settings.linkedin_client_secret')}
                        />
                        <TextInput
                            id="linkedin_client_secret"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.linkedin_client_secret}
                            placeholder={secretPlaceholder(
                                credentials.has_linkedin_client_secret,
                            )}
                            onChange={(e) =>
                                setData('linkedin_client_secret', e.target.value)
                            }
                            autoComplete="off"
                        />
                        <InputError
                            message={errors.linkedin_client_secret}
                            className="mt-2"
                        />
                    </div>
                </fieldset>
            )}

            {showYoutube && (
                <fieldset
                    className={
                        'space-y-4 ' +
                        (showMeta || showGoogle || showTiktok || showLinkedin
                            ? 'border-t border-sp-border pt-6'
                            : '')
                    }
                >
                    <legend className="text-sm font-semibold text-sp-ink">
                        {t('settings.youtube')}
                    </legend>

                    <div>
                        <InputLabel
                            htmlFor="youtube_client_id"
                            value={t('settings.youtube_client_id')}
                        />
                        <TextInput
                            id="youtube_client_id"
                            className="mt-1 block w-full"
                            value={data.youtube_client_id}
                            onChange={(e) =>
                                setData('youtube_client_id', e.target.value)
                            }
                        />
                        <InputError
                            message={errors.youtube_client_id}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="youtube_client_secret"
                            value={t('settings.youtube_client_secret')}
                        />
                        <TextInput
                            id="youtube_client_secret"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.youtube_client_secret}
                            placeholder={secretPlaceholder(
                                credentials.has_youtube_client_secret,
                            )}
                            onChange={(e) =>
                                setData('youtube_client_secret', e.target.value)
                            }
                            autoComplete="off"
                        />
                        <InputError
                            message={errors.youtube_client_secret}
                            className="mt-2"
                        />
                    </div>
                </fieldset>
            )}

            <PrimaryButton disabled={processing}>
                {t('settings.integrations_save')}
            </PrimaryButton>
        </form>
    );
}
