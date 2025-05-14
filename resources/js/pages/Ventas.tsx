import React, { useState } from 'react';
import {
    BarChart, Bar, LineChart, Line, ComposedChart, Area,
    XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
    ReferenceLine, Label, Brush, Cell, TooltipProps
} from 'recharts';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

const SalesDashboard = () => {
    // Monthly sales data with various metrics
    const monthlyData = [
        { period: 'Jan', sales: 4000, targetSales: 3800, profit: 1200, expenses: 2800, customers: 125, salesPerCustomer: 32 },
        { period: 'Feb', sales: 3000, targetSales: 3800, profit: 900, expenses: 2100, customers: 98, salesPerCustomer: 30.6 },
        { period: 'Mar', sales: 5000, targetSales: 4000, profit: 1700, expenses: 3300, customers: 145, salesPerCustomer: 34.5 },
        { period: 'Apr', sales: 2780, targetSales: 4000, profit: 750, expenses: 2030, customers: 87, salesPerCustomer: 32 },
        { period: 'May', sales: 1890, targetSales: 3500, profit: 450, expenses: 1440, customers: 65, salesPerCustomer: 29.1 },
        { period: 'Jun', sales: 2390, targetSales: 3500, profit: 680, expenses: 1710, customers: 78, salesPerCustomer: 30.6 },
        { period: 'Jul', sales: 3490, targetSales: 3600, profit: 1050, expenses: 2440, customers: 110, salesPerCustomer: 31.7 },
        { period: 'Aug', sales: 4200, targetSales: 3600, profit: 1350, expenses: 2850, customers: 130, salesPerCustomer: 32.3 },
        { period: 'Sep', sales: 5100, targetSales: 4200, profit: 1800, expenses: 3300, customers: 155, salesPerCustomer: 32.9 },
        { period: 'Oct', sales: 4300, targetSales: 4200, profit: 1380, expenses: 2920, customers: 135, salesPerCustomer: 31.9 },
        { period: 'Nov', sales: 3800, targetSales: 4000, profit: 1180, expenses: 2620, customers: 120, salesPerCustomer: 31.7 },
        { period: 'Dec', sales: 5900, targetSales: 5000, profit: 2100, expenses: 3800, customers: 180, salesPerCustomer: 32.8 },
    ];

    // Weekly data (simulated)
    const weeklyData = [
        { period: 'W1', sales: 980, targetSales: 950, profit: 300, expenses: 680, customers: 31, salesPerCustomer: 31.6 },
        { period: 'W2', sales: 1200, targetSales: 950, profit: 390, expenses: 810, customers: 38, salesPerCustomer: 31.6 },
        { period: 'W3', sales: 850, targetSales: 950, profit: 240, expenses: 610, customers: 27, salesPerCustomer: 31.5 },
        { period: 'W4', sales: 970, targetSales: 950, profit: 270, expenses: 700, customers: 29, salesPerCustomer: 33.4 },
        { period: 'W5', sales: 720, targetSales: 750, profit: 170, expenses: 550, customers: 23, salesPerCustomer: 31.3 },
        { period: 'W6', sales: 800, targetSales: 750, profit: 230, expenses: 570, customers: 26, salesPerCustomer: 30.8 },
        { period: 'W7', sales: 650, targetSales: 750, profit: 150, expenses: 500, customers: 21, salesPerCustomer: 31.0 },
        { period: 'W8', sales: 830, targetSales: 750, profit: 240, expenses: 590, customers: 28, salesPerCustomer: 29.6 },
    ];

    // Daily data (simulated)
    const dailyData = [
        { period: 'Mon', sales: 420, targetSales: 400, profit: 125, expenses: 295, customers: 14, salesPerCustomer: 30 },
        { period: 'Tue', sales: 380, targetSales: 400, profit: 110, expenses: 270, customers: 12, salesPerCustomer: 31.7 },
        { period: 'Wed', sales: 450, targetSales: 400, profit: 140, expenses: 310, customers: 15, salesPerCustomer: 30 },
        { period: 'Thu', sales: 390, targetSales: 450, profit: 115, expenses: 275, customers: 13, salesPerCustomer: 30 },
        { period: 'Fri', sales: 480, targetSales: 450, profit: 150, expenses: 330, customers: 16, salesPerCustomer: 30 },
        { period: 'Sat', sales: 520, targetSales: 500, profit: 170, expenses: 350, customers: 17, salesPerCustomer: 30.6 },
        { period: 'Sun', sales: 350, targetSales: 300, profit: 90, expenses: 260, customers: 10, salesPerCustomer: 35 },
    ];

    // Time range and chart type state
    const [timeRange, setTimeRange] = useState('monthly');
    const [chartType, setChartType] = useState('bar');

    // Get the appropriate data based on the selected time range
    const getData = () => {
        switch (timeRange) {
            case 'daily': return dailyData;
            case 'weekly': return weeklyData;
            case 'monthly': return monthlyData;
            default: return monthlyData;
        }
    };

    const data = getData();

    // Calculate key metrics
    const totalSales = data.reduce((sum, item) => sum + item.sales, 0);
    const avgSales = (totalSales / data.length).toFixed(0);
    const highestPeriod = [...data].sort((a, b) => b.sales - a.sales)[0];
    const lowestPeriod = [...data].sort((a, b) => a.sales - b.sales)[0];
    const totalProfit = data.reduce((sum, item) => sum + item.profit, 0);
    const profitMargin = (totalProfit / totalSales * 100).toFixed(1);

    // Find periods above/below target
    const periodsAboveTarget = data.filter(period => period.sales > period.targetSales).length;
    const periodsBelowTarget = data.length - periodsAboveTarget;

    // Get time period label (singular)
    const getPeriodLabel = () => {
        switch (timeRange) {
            case 'daily': return 'Day';
            case 'weekly': return 'Week';
            case 'monthly': return 'Month';
            default: return 'Period';
        }
    };

    // Custom currency formatter
    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    };

    // Format for y-axis labels
    const formatYAxis = (value: number) => {
        if (value >= 1000) {
            return `$${(value / 1000).toFixed(0)}k`;
        }
        return `$${value}`;
    };

    // Color palette - Updated colors
    const colors = {
        sales: '#3b82f6',           // Changed from green to blue
        salesAboveTarget: '#3b82f6', // Changed from green to blue
        salesBelowTarget: '#f97316', // Changed from red to orange
        target: '#8b5cf6',          // Changed from blue to purple
        profit: '#ec4899',          // Changed from purple to pink
        expenses: '#f43f5e',        // Changed from red to rose
        average: '#14b8a6',         // Changed from yellow to teal
        brush: '#8b5cf6'            // Changed from blue to purple
    };

    // Determine if a period exceeded target (for coloring)
    type SalesEntry = {
        sales: number;
        targetSales: number;
    };
    const getBarColor = (entry: SalesEntry) => {
        return entry.sales >= entry.targetSales ? colors.salesAboveTarget : colors.salesBelowTarget;
    };

    // Custom tooltip with detailed information
    type TimeRange = 'daily' | 'weekly' | 'monthly';

    interface ChartDataPoint {
        period: string;
        sales: number;
        targetSales: number;
        profit: number;
        expenses: number;
        customers: number;
        salesPerCustomer: number;
    }

    interface CustomTooltipProps extends TooltipProps<number, string> {
        data: ChartDataPoint[];
        timeRange: TimeRange;
        formatCurrency: (value: number) => string;
    }

    const CustomTooltip: React.FC<CustomTooltipProps> = ({
        active,
        payload,
        label,
        data,
        timeRange,
        formatCurrency,
    }) => {
        if (active && payload && payload.length) {
            const dataPoint = data.find((item) => item.period === label);
            if (!dataPoint) return null;

            const targetDiff = dataPoint.sales - dataPoint.targetSales;
            const targetPercentage = ((dataPoint.sales / dataPoint.targetSales) * 100).toFixed(1);

            return (
                <div className="bg-white p-3 border border-gray-300 shadow-lg rounded">
                    <p className="font-bold text-gray-800 text-lg border-b pb-1 mb-2">{`${label} ${timeRange === 'monthly' ? '2025' : ''
                        }`}</p>
                    <p className="text-blue-600 font-semibold">{`Sales: ${formatCurrency(payload[0].value as number)}`}</p>
                    <p className="text-purple-600">{`Target: ${formatCurrency(dataPoint.targetSales)}`}</p>
                    <p className={targetDiff >= 0 ? 'text-blue-500' : 'text-orange-500'}>
                        {`Vs Target: ${targetDiff >= 0 ? '+' : ''}${formatCurrency(targetDiff)} (${targetPercentage}%)`}
                    </p>
                    <p className="text-pink-600">{`Profit: ${formatCurrency(dataPoint.profit)}`}</p>
                    <p className="text-rose-600">{`Expenses: ${formatCurrency(dataPoint.expenses)}`}</p>
                    <p className="text-gray-600">{`Customers: ${dataPoint.customers}`}</p>
                    <p className="text-gray-600">{`Avg Sale: ${formatCurrency(dataPoint.salesPerCustomer)}`}</p>
                </div>
            );
        }

        return null;
    };

    // Render the selected chart type
    const renderChart = (): JSX.Element => {
        switch (chartType) {
            case 'bar':
                return (
                    <BarChart
                        data={data}
                        margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                        <XAxis dataKey="period" tick={{ fill: '#666' }} />
                        <YAxis
                            tickFormatter={formatYAxis}
                            tick={{ fill: '#666' }}
                        >
                            <Label value="Sales" angle={-90} position="insideLeft" style={{ textAnchor: 'middle', fill: '#666' }} />
                        </YAxis>
                        <Tooltip
                        // content={(props) => (
                        //     <CustomTooltip
                        //         {...props}
                        //         data={data}
                        //         timeRange={timeRange}
                        //         formatCurrency={formatCurrency}
                        //     />
                        // )}
                        />
                        <Legend />
                        <ReferenceLine y={avgSales} stroke={colors.average} strokeDasharray="3 3">
                            <Label value="Average" position="right" fill={colors.average} />
                        </ReferenceLine>
                        <Bar dataKey="sales" name={`${timeRange.charAt(0).toUpperCase() + timeRange.slice(1, -2)} Sales`} animationDuration={1500}>
                            {data.map((entry, index) => (
                                <Cell key={`cell-${index}`} fill={getBarColor(entry)} />
                            ))}
                        </Bar>
                        <Bar dataKey="targetSales" name="Target" fill={colors.target} opacity={0.6} />
                        {data.length > 7 && <Brush dataKey="period" height={30} stroke={colors.brush} />}
                    </BarChart>
                );
            case 'line':
                return (
                    <LineChart
                        data={data}
                        margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                        <XAxis dataKey="period" tick={{ fill: '#666' }} />
                        <YAxis
                            tickFormatter={formatYAxis}
                            tick={{ fill: '#666' }}
                        >
                            <Label value="Amount" angle={-90} position="insideLeft" style={{ textAnchor: 'middle', fill: '#666' }} />
                        </YAxis>
                        <Tooltip
                        // content={<CustomTooltip />}
                        />
                        <Legend />
                        <Line type="monotone" dataKey="sales" name="Sales" stroke={colors.sales} strokeWidth={2} dot={{ r: 4 }} activeDot={{ r: 8 }} />
                        <Line type="monotone" dataKey="targetSales" name="Target" stroke={colors.target} strokeWidth={2} strokeDasharray="5 5" />
                        <Line type="monotone" dataKey="profit" name="Profit" stroke={colors.profit} strokeWidth={2} />
                        <Line type="monotone" dataKey="expenses" name="Expenses" stroke={colors.expenses} strokeWidth={2} />
                        {data.length > 7 && <Brush dataKey="period" height={30} stroke={colors.brush} />}
                    </LineChart>
                );
            case 'area':
                return (
                    <ComposedChart
                        data={data}
                        margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                        <XAxis dataKey="period" tick={{ fill: '#666' }} />
                        <YAxis
                            tickFormatter={formatYAxis}
                            tick={{ fill: '#666' }}
                        >
                            <Label value="Amount" angle={-90} position="insideLeft" style={{ textAnchor: 'middle', fill: '#666' }} />
                        </YAxis>
                        <Tooltip
                        //  content={<CustomTooltip />}
                        />
                        <Legend />
                        <Area type="monotone" dataKey="sales" name="Sales" fill={colors.sales} stroke={colors.sales} fillOpacity={0.4} />
                        <Line type="monotone" dataKey="targetSales" name="Target" stroke={colors.target} strokeWidth={2} strokeDasharray="5 5" />
                        <Bar dataKey="profit" name="Profit" barSize={20} fill={colors.profit} />
                        {data.length > 7 && <Brush dataKey="period" height={30} stroke={colors.brush} />}
                    </ComposedChart>
                );
            default:
                return <div>No chart available</div>;
        }
    };

    return (
        <DashboardLayout>
        <div className="p-6 bg-white rounded-xl shadow-lg">
            <div className="flex flex-wrap items-center justify-between gap-4 mb-6 border-b pb-4">
                <h2 className="text-2xl font-bold text-gray-800 flex items-center">
                    <span className="text-blue-600 mr-2">$</span>
                    {timeRange.charAt(0).toUpperCase() + timeRange.slice(1)} Sales Dashboard
                </h2>

                {/* Time Range Selector */}
                <div className="flex border rounded-lg overflow-hidden">
                    <button
                        onClick={() => setTimeRange('daily')}
                        className={`px-3 py-2 ${timeRange === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
                    >
                        Daily
                    </button>
                    <button
                        onClick={() => setTimeRange('weekly')}
                        className={`px-3 py-2 ${timeRange === 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
                    >
                        Weekly
                    </button>
                    <button
                        onClick={() => setTimeRange('monthly')}
                        className={`px-3 py-2 ${timeRange === 'monthly' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
                    >
                        Monthly
                    </button>
                </div>
            </div>

            {/* Key Metrics Grid */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200 shadow-sm">
                    <div className="text-sm text-gray-600">Total Sales</div>
                    <div className="text-2xl font-bold text-blue-600">{formatCurrency(totalSales)}</div>
                </div>
                <div className="bg-gradient-to-br from-pink-50 to-pink-100 p-4 rounded-lg border border-pink-200 shadow-sm">
                    <div className="text-sm text-gray-600">Total Profit</div>
                    <div className="text-2xl font-bold text-pink-600">{formatCurrency(totalProfit)}</div>
                </div>
                <div className="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200 shadow-sm">
                    <div className="text-sm text-gray-600">Profit Margin</div>
                    <div className="text-2xl font-bold text-purple-600">{profitMargin}%</div>
                </div>
                <div className="bg-gradient-to-br from-teal-50 to-teal-100 p-4 rounded-lg border border-teal-200 shadow-sm">
                    <div className="text-sm text-gray-600">Avg {getPeriodLabel()} Sales</div>
                    {/* <div className="text-2xl font-bold text-teal-600">{formatCurrency(avgSales)}</div> */}
                </div>
            </div>

            {/* Chart Type Selector */}
            <div className="flex flex-wrap gap-2 mb-6">
                <button
                    onClick={() => setChartType('bar')}
                    className={`px-3 py-1 rounded-full ${chartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
                >
                    Bar Chart
                </button>
                <button
                    onClick={() => setChartType('line')}
                    className={`px-3 py-1 rounded-full ${chartType === 'line' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
                >
                    Line Chart
                </button>
                <button
                    onClick={() => setChartType('area')}
                    className={`px-3 py-1 rounded-full ${chartType === 'area' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
                >
                    Area Chart
                </button>
            </div>

            {/* Performance Highlights */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div className="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                    <div className="text-sm text-gray-500">Best {getPeriodLabel()}</div>
                    <div className="text-lg font-bold text-blue-600">{highestPeriod.period}</div>
                    <div className="text-sm text-gray-700">{formatCurrency(highestPeriod.sales)}</div>
                </div>
                <div className="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                    <div className="text-sm text-gray-500">Lowest {getPeriodLabel()}</div>
                    <div className="text-lg font-bold text-orange-600">{lowestPeriod.period}</div>
                    <div className="text-sm text-gray-700">{formatCurrency(lowestPeriod.sales)}</div>
                </div>
                <div className="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                    <div className="text-sm text-gray-500">{getPeriodLabel()}s Above Target</div>
                    <div className="text-lg font-bold text-blue-600">{periodsAboveTarget}</div>
                    <div className="text-sm text-gray-700">of {data.length} {getPeriodLabel().toLowerCase()}s</div>
                </div>
                <div className="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                    <div className="text-sm text-gray-500">{getPeriodLabel()}s Below Target</div>
                    <div className="text-lg font-bold text-orange-600">{periodsBelowTarget}</div>
                    <div className="text-sm text-gray-700">of {data.length} {getPeriodLabel().toLowerCase()}s</div>
                </div>
            </div>

            {/* Chart */}
            <div className="border rounded-lg p-4 bg-gray-50">
                <ResponsiveContainer width="100%" height={400}>
                    {renderChart()}
                </ResponsiveContainer>
            </div>

            <div className="mt-6 text-center text-sm text-gray-600">
                <p>Data represents sample {timeRange} sales figures for 2025</p>
                <p className="mt-1 text-xs text-gray-500">Blue bars indicate periods where sales exceeded targets</p>
            </div>
        </div>
        </DashboardLayout>
    );
};

export default SalesDashboard;
