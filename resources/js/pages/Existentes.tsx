import React from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

const Existentes = () => {
    return (
        <DashboardLayout>
            <div className="bg-gray-50 p-6 rounded-lg shadow-lg max-w-4xl mx-auto">
                {/* Main KPIs */}
                <div className="grid grid-cols-4 gap-4 mb-8">
                    <KpiCard
                        title="Total Sales"
                        value="$445,897,635"
                        color="bg-indigo-600"
                        icon="📈"
                    />
                    <KpiCard
                        title="Mixed Cost"
                        value="24.52%"
                        color="bg-amber-500"
                        icon="⚖️"
                    />
                    <KpiCard
                        title="Net Profit"
                        value="$108,084,197"
                        color="bg-emerald-600"
                        icon="💰"
                    />
                    <KpiCard
                        title="Profit Margin"
                        value="24%"
                        color="bg-blue-600"
                        icon="📊"
                    />
                </div>

                {/* Monthly Performance */}
                <div className="mb-8">
                    <h2 className="text-xl font-bold mb-4 text-gray-800">Monthly Performance (Q4 2024)</h2>
                    <div className="bg-white p-4 rounded-lg shadow">
                        <table className="min-w-full">
                            <thead>
                                <tr className="border-b border-gray-200">
                                    <th className="text-left py-3 px-4 font-medium text-gray-700">Indicator</th>
                                    <th className="text-right py-3 px-4 font-medium text-gray-700">October</th>
                                    <th className="text-right py-3 px-4 font-medium text-gray-700">November</th>
                                    <th className="text-right py-3 px-4 font-medium text-gray-700">December</th>
                                </tr>
                            </thead>
                            <tbody>
                                <TableRow label="Sales"
                                    oct="$143,074,957"
                                    nov="$155,839,529"
                                    dec="$146,983,149"
                                    highlightBest={true}
                                />
                                <TableRow label="Total Expenses"
                                    oct="$103,734,434"
                                    nov="$88,045,938"
                                    dec="$122,394,190"
                                    highlightBest={false}
                                />
                                <TableRow label="Gross Profit"
                                    oct="$39,340,523"
                                    nov="$67,793,591"
                                    dec="$24,588,959"
                                    highlightBest={true}
                                />
                                <TableRow label="Net Profit"
                                    oct="$35,591,750"
                                    nov="$59,503,980"
                                    dec="$12,988,466"
                                    highlightBest={true}
                                />
                                <TableRow label="Profit Margin"
                                    oct="24.88%"
                                    nov="38.18%"
                                    dec="8.84%"
                                    highlightBest={true}
                                />
                                <TableRow label="Mixed Cost"
                                    oct="25.09%"
                                    nov="16.87%"
                                    dec="29.16%"
                                    highlightBest={false}
                                />
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Additional Metrics */}
                <div className="grid grid-cols-2 gap-6">
                    <div className="bg-white p-4 rounded-lg shadow">
                        <h2 className="text-lg font-bold mb-3 text-gray-800">Cost Structure</h2>
                        <div className="space-y-4">
                            <MetricRow label="Food Cost %" value="31.61%" />
                            <MetricRow label="Beverage Cost %" value="20.60%" />
                            <MetricRow label="Mixed Cost %" value="24.52%" />
                            <div className="pt-2 border-t border-gray-100">
                                <h3 className="font-semibold text-sm text-gray-600 mb-2">Monthly Mixed Cost Trend</h3>
                                <div className="flex items-center gap-2">
                                    <span className="px-2 py-1 bg-amber-100 text-amber-800 rounded text-sm">Oct: 25.09%</span>
                                    <span className="text-green-500">↓</span>
                                    <span className="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Nov: 16.87%</span>
                                    <span className="text-red-500">↑</span>
                                    <span className="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Dec: 29.16%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white p-4 rounded-lg shadow">
                        <h2 className="text-lg font-bold mb-3 text-gray-800">Financial Operations</h2>
                        <div className="space-y-4">
                            <MetricRow label="Total Investments" value="$13,578,268" />
                            <MetricRow label="Dividend Payments" value="$125,870,000" />
                            <MetricRow label="Overdue Debt Payments" value="$595,000" />
                            <div className="pt-2 border-t border-gray-100">
                                <h3 className="font-semibold text-sm text-gray-600 mb-2">Performance Analysis</h3>
                                <div className="flex flex-col gap-2">
                                    <span className="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Best Month: November (38.18% margin)</span>
                                    <span className="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Worst Month: December (8.84% margin)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
};

// Component for KPI cards
type KpiCardProps = {
    title: string;
    value: number | string;
    color: string;
    icon: React.ReactNode;
};
const KpiCard = ({ title, value, color, icon }: KpiCardProps) => {
    return (
        <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className={`${color} h-2`}></div>
            <div className="p-4">
                <div className="flex items-center justify-between mb-2">
                    <span className="text-gray-500 text-sm font-medium">{title}</span>
                    <span className="text-2xl">{icon}</span>
                </div>
                <div className="text-2xl font-bold text-gray-800">{value}</div>
            </div>
        </div>
    );
};

// Component for table rows
type TableRowProps = {
    label: string;
    oct: string; // 통화 포맷 등 문자열
    nov: string;
    dec: string;
    highlightBest: boolean;
};

const TableRow = ({ label, oct, nov, dec, highlightBest }: TableRowProps) => {
    const values = [oct, nov, dec];

    const bestIndex = highlightBest
        ? values.indexOf(Math.max(...values.map(v => parseFloat(v.replace(/[^0-9.-]+/g, '')))).toString())
        : values.indexOf(Math.min(...values.map(v => parseFloat(v.replace(/[^0-9.-]+/g, '')))).toString());

    return (
        <tr className="border-b border-gray-100">
            <td className="py-3 px-4 text-gray-800">{label}</td>
            <td className={`py-3 px-4 text-right ${bestIndex === 0 ? 'font-bold text-green-600' : 'text-gray-700'}`}>{oct}</td>
            <td className={`py-3 px-4 text-right ${bestIndex === 1 ? 'font-bold text-green-600' : 'text-gray-700'}`}>{nov}</td>
            <td className={`py-3 px-4 text-right ${bestIndex === 2 ? 'font-bold text-green-600' : 'text-gray-700'}`}>{dec}</td>
        </tr>
    );
};

// Component for metric rows
type MetricRowProps = {
    label: string;
    value: string | number;
};

const MetricRow = ({ label, value }: MetricRowProps) => {
    return (
        <div className="flex items-center justify-between">
            <span className="text-gray-600">{label}</span>
            <span className="font-semibold text-gray-800">{value}</span>
        </div>
    );
};


export default Existentes;
