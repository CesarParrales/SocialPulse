import { SVGAttributes } from 'react';

type ApplicationLogoProps = SVGAttributes<SVGElement> & {
    showWordmark?: boolean;
    variant?: 'light' | 'dark';
};

export default function ApplicationLogo({
    showWordmark = false,
    variant = 'dark',
    className = '',
    ...props
}: ApplicationLogoProps) {
    const textClass =
        variant === 'light' ? 'text-white' : 'text-sp-ink';

    return (
        <div className={`flex items-center gap-2.5 ${className}`}>
            <svg
                {...props}
                viewBox="0 0 40 40"
                xmlns="http://www.w3.org/2000/svg"
                className="h-9 w-9 shrink-0"
                aria-hidden="true"
            >
                <defs>
                    <linearGradient id="sp-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stopColor="#6D28D9" />
                        <stop offset="100%" stopColor="#EC4899" />
                    </linearGradient>
                </defs>
                <rect width="40" height="40" rx="10" fill="url(#sp-gradient)" />
                <path
                    d="M8 22 C12 14, 16 28, 20 18 C24 8, 28 24, 32 16"
                    fill="none"
                    stroke="white"
                    strokeWidth="2.5"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
                <circle cx="32" cy="16" r="2.5" fill="white" />
            </svg>
            {showWordmark && (
                <span className={`text-lg font-bold tracking-tight ${textClass}`}>
                    Social<span className="text-sp-primary">Pulse</span>
                </span>
            )}
        </div>
    );
}
