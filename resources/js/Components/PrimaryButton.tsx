import { ButtonHTMLAttributes } from 'react';

export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-lg border border-transparent bg-sp-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sp-primary-hover focus:outline-none focus:ring-2 focus:ring-sp-primary focus:ring-offset-2 active:bg-sp-primary-hover disabled:opacity-50 ` +
                className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
