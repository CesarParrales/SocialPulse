import { ButtonHTMLAttributes } from 'react';

export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            type={type}
            className={
                `inline-flex items-center rounded-lg border border-sp-border bg-white px-4 py-2.5 text-sm font-semibold text-sp-ink shadow-sm transition hover:bg-sp-surface focus:outline-none focus:ring-2 focus:ring-sp-primary focus:ring-offset-2 disabled:opacity-50 ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
