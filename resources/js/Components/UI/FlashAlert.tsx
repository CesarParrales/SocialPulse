export default function FlashAlert({
    message,
    variant = 'success',
    className = '',
}: {
    message: string;
    variant?: 'success' | 'error';
    className?: string;
}) {
    const styles =
        variant === 'error'
            ? 'border-red-200 bg-red-50 text-red-800'
            : 'border-emerald-200 bg-emerald-50 text-emerald-800';

    return (
        <div
            role="status"
            className={`rounded-lg border p-4 text-sm ${styles} ${className}`}
        >
            {message}
        </div>
    );
}
