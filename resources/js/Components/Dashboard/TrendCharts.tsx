import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

type DailyReach = { date: string; organic: number; paid: number };
type DailySpend = { date: string; spend: number };
type DailyCommunity = { date: string; total: number };

export default function TrendCharts({
    dailyReach,
    dailySpend,
    dailyCommunity,
}: {
    dailyReach: DailyReach[];
    dailySpend: DailySpend[];
    dailyCommunity: DailyCommunity[];
}) {
    if (dailyReach.length === 0) {
        return (
            <p className="text-sm text-gray-500">
                No hay datos de tendencia para el período seleccionado.
            </p>
        );
    }

    const shortDate = (value: string | number) =>
        new Date(String(value)).toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
        });

    return (
        <div className="grid gap-6 lg:grid-cols-2">
            <ChartCard title="Reach diario (orgánico vs pagado)">
                <ResponsiveContainer width="100%" height={260}>
                    <LineChart data={dailyReach}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="date" tickFormatter={shortDate} />
                        <YAxis />
                        <Tooltip
                            labelFormatter={(label) => shortDate(String(label))}
                        />
                        <Legend />
                        <Line
                            type="monotone"
                            dataKey="organic"
                            name="Orgánico"
                            stroke="#4f46e5"
                            strokeWidth={2}
                            dot={false}
                        />
                        <Line
                            type="monotone"
                            dataKey="paid"
                            name="Pagado"
                            stroke="#059669"
                            strokeWidth={2}
                            dot={false}
                        />
                    </LineChart>
                </ResponsiveContainer>
            </ChartCard>

            <ChartCard title="Inversión diaria">
                <ResponsiveContainer width="100%" height={260}>
                    <BarChart data={dailySpend}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="date" tickFormatter={shortDate} />
                        <YAxis />
                        <Tooltip
                            labelFormatter={(label) => shortDate(String(label))}
                        />
                        <Bar dataKey="spend" name="Spend" fill="#6366f1" />
                    </BarChart>
                </ResponsiveContainer>
            </ChartCard>

            <ChartCard title="Comunidad (seguidores + fans)">
                <ResponsiveContainer width="100%" height={260}>
                    <AreaChart data={dailyCommunity}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="date" tickFormatter={shortDate} />
                        <YAxis />
                        <Tooltip
                            labelFormatter={(label) => shortDate(String(label))}
                        />
                        <Area
                            type="monotone"
                            dataKey="total"
                            name="Total"
                            stroke="#7c3aed"
                            fill="#ddd6fe"
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </ChartCard>
        </div>
    );
}

function ChartCard({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <div className="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <h3 className="mb-4 text-lg font-medium text-gray-900">{title}</h3>
            {children}
        </div>
    );
}
