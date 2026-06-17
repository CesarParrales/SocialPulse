type KpiComparison = {
    current: number;
    previous: number;
    change_pct: number | null;
    direction: 'up' | 'down' | 'flat';
    comparable: boolean;
};

export default function KpiCard({
    label,
    value,
    comparison,
    format = 'number',
    hint,
}: {
    label: string;
    value: number;
    comparison: KpiComparison;
    format?: 'number' | 'currency' | 'percent';
    hint?: string;
}) {
    const formatted = formatValue(value, format);

    return (
        <div className="sp-card relative overflow-hidden p-5">
            <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sp-primary to-sp-accent" />
            <p className="text-sm font-medium text-sp-muted">{label}</p>
            <p className="mt-1 text-2xl font-bold tracking-tight text-sp-ink">
                {formatted}
            </p>
            {hint && <p className="mt-1 text-xs text-sp-muted">{hint}</p>}
            <ComparisonBadge comparison={comparison} format={format} />
        </div>
    );
}

function formatValue(value: number, format: 'number' | 'currency' | 'percent'): string {
    if (format === 'currency') {
        return `$${value.toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
    }

    if (format === 'percent') {
        return `${value.toFixed(1)}%`;
    }

    return value.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function ComparisonBadge({
    comparison,
    format,
}: {
    comparison: KpiComparison;
    format: 'number' | 'currency' | 'percent';
}) {
    if (!comparison.comparable) {
        return (
            <p className="mt-2 text-xs text-amber-600">
                Sin histórico suficiente para comparar
            </p>
        );
    }

    if (comparison.change_pct === null) {
        return null;
    }

    const arrow =
        comparison.direction === 'up'
            ? '↑'
            : comparison.direction === 'down'
              ? '↓'
              : '→';

    const color =
        comparison.direction === 'up'
            ? 'text-emerald-700'
            : comparison.direction === 'down'
              ? 'text-rose-700'
              : 'text-sp-muted';

    const previousLabel = formatValue(comparison.previous, format);

    return (
        <p className={`mt-2 text-xs font-medium ${color}`}>
            {arrow} {Math.abs(comparison.change_pct).toFixed(1)}% vs período anterior (
            {previousLabel})
        </p>
    );
}
