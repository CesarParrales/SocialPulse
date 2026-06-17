export default function AuthFormHeader({
    title,
    description,
}: {
    title: string;
    description?: string;
}) {
    return (
        <div className="mb-6">
            <h1 className="text-xl font-semibold tracking-tight text-sp-ink">
                {title}
            </h1>
            {description && (
                <p className="mt-2 text-sm leading-relaxed text-sp-muted">
                    {description}
                </p>
            )}
        </div>
    );
}
