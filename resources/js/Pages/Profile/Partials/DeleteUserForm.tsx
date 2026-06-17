import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import { useTranslation } from '@/lib/i18n';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';

export default function DeleteUserForm({
    className = '',
}: {
    className?: string;
}) {
    const { t } = useTranslation();
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);

        clearErrors();
        reset();
    };

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-lg font-semibold text-red-900">
                    {t('profile.delete_title')}
                </h2>

                <p className="mt-1 text-sm text-red-800/80">
                    {t('profile.delete_description')}
                </p>
            </header>

            <DangerButton type="button" onClick={confirmUserDeletion}>
                {t('profile.delete_button')}
            </DangerButton>

            <Modal
                show={confirmingUserDeletion}
                onClose={closeModal}
                maxWidth="md"
            >
                <form onSubmit={deleteUser} className="p-6">
                    <h2
                        id="delete-account-title"
                        className="text-lg font-semibold text-sp-ink"
                    >
                        {t('profile.delete_confirm_title')}
                    </h2>

                    <div className="mt-4">
                        <CalloutBanner
                            title={t('profile.delete_warning_title')}
                            variant="warning"
                        >
                            {t('profile.delete_warning')}
                        </CalloutBanner>
                    </div>

                    <p className="mt-4 text-sm text-sp-muted">
                        {t('profile.delete_confirm_description')}
                    </p>

                    <div className="mt-6">
                        <InputLabel
                            htmlFor="delete-password"
                            value={t('profile.current_password')}
                        />

                        <TextInput
                            id="delete-password"
                            type="password"
                            name="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                            className="mt-1 block w-full"
                            isFocused
                            autoComplete="current-password"
                        />

                        <InputError
                            message={errors.password}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-6 flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeModal}>
                            {t('common.cancel')}
                        </SecondaryButton>

                        <DangerButton type="submit" disabled={processing}>
                            {t('profile.delete_button')}
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
