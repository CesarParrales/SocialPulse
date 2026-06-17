import { forwardRef, InputHTMLAttributes } from 'react';

function SearchIcon() {
    return (
        <svg
            className="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={1.75}
            aria-hidden="true"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
        </svg>
    );
}

const SearchInput = forwardRef<
    HTMLInputElement,
    InputHTMLAttributes<HTMLInputElement> & { wrapperClassName?: string }
>(function SearchInput(
    { className = '', wrapperClassName = '', id, ...props },
    ref,
) {
    return (
        <div className={`relative ${wrapperClassName}`}>
            <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sp-muted">
                <SearchIcon />
            </span>
            <input
                {...props}
                ref={ref}
                id={id}
                type="search"
                className={
                    'sp-input block w-full rounded-lg border-sp-border py-2.5 pl-10 pr-4 text-sm shadow-sm focus:border-sp-primary focus:ring-sp-primary ' +
                    className
                }
            />
        </div>
    );
});

export default SearchInput;
