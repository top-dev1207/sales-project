import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

// Types
type PeriodType = 'daily' | 'weekly' | 'monthly';

interface DateRange {
    startDate: string;
    endDate: string;
}

interface ExpenseByIncidence {
    rubro_id: number;
    rubro_nombre: string;
    importe: number;
    porcentaje: number;
    orden: number;
}

interface PeriodDisplay {
    type: string;
    display: string;
    start_date: string; // Added to ensure start date is available
    end_date: string;   // Added to ensure end date is available
    [key: string]: any;
}

interface ExpensePeriod {
    period: PeriodDisplay;
    total: number;
    expenses: ExpenseByIncidence[];
}

interface ExpensesResponse {
    status: string;
    period: {
        type: PeriodType;
        start_date: string;
        end_date: string;
    };
    data: ExpensePeriod[];
}

interface PeriodComparison {
    group_id: number;
    group_name: string;
    period1_amount: number;
    period2_amount: number;
    difference: number;
    difference_percentage: number | null;
}

interface ComparisonResponse {
    status: string;
    period1: {
        start_date: string;
        end_date: string;
        total: number;
    };
    period2: {
        start_date: string;
        end_date: string;
        total: number;
    };
    comparison: PeriodComparison[];
}

// Colors for charts
const COLORS = [
    '#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8',
    '#82CA9D', '#A4DE6C', '#D0ED57', '#F56C42', '#E4811C',
    '#8DD1E1', '#FFC658', '#83A6ED', '#8C7AE6', '#FF99E6'
];

/**
 * Format a number as currency (ARS)
 */
const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0
    }).format(value);
};

/**
 * Format a date range string based on period type
 */
const formatDateRange = (startDate: string, endDate: string, periodType: PeriodType): string => {
    // Parse dates
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Format options
    const dateOptions: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };

    // Format based on period type
    if (periodType === 'weekly') {
        // For weekly view, explicitly show start and end date
        return `${start.toLocaleDateString('en-US', dateOptions)} - ${end.toLocaleDateString('en-US', dateOptions)}`;
    } else if (periodType === 'monthly') {
        // For monthly view
        return start.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    } else {
        // For daily view
        return start.toLocaleDateString('en-US', dateOptions);
    }
};

const Gastos: React.FC = () => {
    // States
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);
    const [activeTab, setActiveTab] = useState<'incidence' | 'comparison'>('incidence');

    // States for "incidence" tab
    const [periodType, setPeriodType] = useState<PeriodType>('monthly');
    const [dateRange, setDateRange] = useState<DateRange>({
        startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 2, 1).toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0]
    });
    const [limit, setLimit] = useState<number>(10);
    const [incidenceData, setIncidenceData] = useState<ExpensePeriod[]>([]);
    const [selectedPeriod, setSelectedPeriod] = useState<number>(0);

    // States for "comparison" tab
    const [comparisonPeriodType, setComparisonPeriodType] = useState<PeriodType>('monthly');
    const [period1, setPeriod1] = useState<DateRange>({
        startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 2, 1).toISOString().split('T')[0],
        endDate: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 0).toISOString().split('T')[0]
    });
    const [period2, setPeriod2] = useState<DateRange>({
        startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0]
    });
    const [groupBy, setGroupBy] = useState<'rubro' | 'category'>('rubro');
    const [comparisonData, setComparisonData] = useState<PeriodComparison[]>([]);
    const [periodTotals, setPeriodTotals] = useState<{ period1: number, period2: number }>({ period1: 0, period2: 0 });

    // Effect to load initial data
    useEffect(() => {
        if (activeTab === 'incidence') {
            fetchExpensesByIncidence();
        }
    }, [activeTab]);

    /**
     * Fetch expenses by incidence from API
     */
    const fetchExpensesByIncidence = async () => {
        setLoading(true);
        setError(null);

        try {
            const queryParams = new URLSearchParams({
                period_type: periodType,
                start_date: dateRange.startDate,
                end_date: dateRange.endDate,
                limit: limit.toString()
            });

            const response = await fetch(`/api/expenses-analysis/by-incidence?${queryParams}`);

            if (!response.ok) {
                throw new Error('Error fetching expense incidence data');
            }

            const data: ExpensesResponse = await response.json();

            if (data.status === 'success') {
                // Make sure the response includes date information for the periods
                const enhancedData = data.data.map(period => {
                    // Ensure the period has explicit start/end dates for display
                    if (!period.period.start_date) {
                        period.period.start_date = period.period.startDate || data.period.start_date;
                    }
                    if (!period.period.end_date) {
                        period.period.end_date = period.period.endDate || data.period.end_date;
                    }

                    // For weekly periods, enhance the display value to clearly show the date range
                    if (data.period.type === 'weekly') {
                        period.period.display = formatDateRange(
                            period.period.start_date,
                            period.period.end_date,
                            'weekly'
                        );
                    }

                    return period;
                });

                setIncidenceData(enhancedData);
                setSelectedPeriod(0); // Select first period by default
            } else {
                throw new Error('Response error: ' + data.status);
            }
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    /**
     * Compare expense periods
     */
    const compareExpensesPeriods = async () => {
        setLoading(true);
        setError(null);

        try {
            const queryParams = new URLSearchParams({
                period_type: comparisonPeriodType,
                period1_start: period1.startDate,
                period1_end: period1.endDate,
                period2_start: period2.startDate,
                period2_end: period2.endDate,
                group_by: groupBy
            });

            const response = await fetch(`/api/expenses-analysis/compare-periods?${queryParams}`);

            if (!response.ok) {
                throw new Error('Error fetching period comparison data');
            }

            const data: ComparisonResponse = await response.json();

            if (data.status === 'success') {
                setComparisonData(data.comparison);
                setPeriodTotals({
                    period1: data.period1.total,
                    period2: data.period2.total
                });
            } else {
                throw new Error('Response error: ' + data.status);
            }
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    const handleTabChange = (tab: 'incidence' | 'comparison') => {
        setActiveTab(tab);
        if (tab === 'comparison' && comparisonData.length === 0) {
            compareExpensesPeriods();
        }
    };

    /**
     * Render the incidence analysis tab
     */
    const renderIncidenceTab = () => {
        return (
            <div className="space-y-6">
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="text-lg font-medium mb-4">Analysis Parameters</h3>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Period Type</label>
                            <select
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                value={periodType}
                                onChange={(e) => setPeriodType(e.target.value as PeriodType)}
                            >
                                <option value="daily">daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input
                                type="date"
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                value={dateRange.startDate}
                                onChange={(e) => setDateRange({ ...dateRange, startDate: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input
                                type="date"
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                value={dateRange.endDate}
                                onChange={(e) => setDateRange({ ...dateRange, endDate: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Item Limit</label>
                            <input
                                type="number"
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                value={limit}
                                min={3}
                                max={50}
                                onChange={(e) => setLimit(parseInt(e.target.value))}
                            />
                        </div>
                    </div>
                    <div className="mt-4">
                        <button
                            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                            onClick={fetchExpensesByIncidence}
                            disabled={loading}
                        >
                            {loading ? 'Loading...' : 'Analyze'}
                        </button>
                    </div>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {error}
                    </div>
                )}

                {incidenceData.length > 0 && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div className="bg-white rounded-lg shadow p-4">
                            <h3 className="text-lg font-medium mb-4">Analyzed Periods</h3>
                            <div className="overflow-x-auto h-72 max-h-72 overflow-y-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {incidenceData.map((period, idx) => (
                                            <tr key={idx} className={selectedPeriod === idx ? 'bg-blue-50' : ''}>
                                                <td className="px-6 py-4 whitespace-nowrap">{period.period.display}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{formatCurrency(period.total)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <button
                                                        className="text-blue-600 hover:text-blue-900"
                                                        onClick={() => setSelectedPeriod(idx)}
                                                    >
                                                        View Details
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {incidenceData[selectedPeriod] && (
                            <div className="bg-white rounded-lg shadow p-4 flex flex-col h-full">
                                <h3 className="text-lg font-medium mb-4">
                                    Period Details: {incidenceData[selectedPeriod].period.display}
                                </h3>

                                <div className="mb-6">
                                    <div className="h-64">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={incidenceData[selectedPeriod].expenses}
                                                    dataKey="porcentaje"
                                                    nameKey="rubro_nombre"
                                                    cx="50%"
                                                    cy="50%"
                                                    outerRadius={80}
                                                    label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(1)}%`}
                                                >
                                                    {incidenceData[selectedPeriod].expenses.map((entry, index) => (
                                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                                    ))}
                                                </Pie>
                                                <Tooltip
                                                    formatter={(value: number) => `${value.toFixed(1)}%`}
                                                />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>

                                <div className="overflow-x-auto overflow-y-auto flex-1" style={{ maxHeight: '300px' }}>
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50 sticky top-0 z-10">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {incidenceData[selectedPeriod].expenses.map((expense, idx) => (
                                                <tr key={idx}>
                                                    <td className="px-6 py-4 whitespace-nowrap">{expense.rubro_nombre}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">{formatCurrency(expense.importe)}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">{expense.porcentaje.toFixed(1)}%</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        );
    };

    /**
     * Render the period comparison tab
     */
    const renderComparisonTab = () => {
        // Format the period labels for display
        const period1Label = formatDateRange(period1.startDate, period1.endDate, comparisonPeriodType);
        const period2Label = formatDateRange(period2.startDate, period2.endDate, comparisonPeriodType);

        return (
            <div className="space-y-6">
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="text-lg font-medium mb-4">Comparison Parameters</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <h4 className="font-medium mb-2">Period 1</h4>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                    <input
                                        type="date"
                                        className="w-full border border-gray-300 rounded-md px-3 py-2"
                                        value={period1.startDate}
                                        onChange={(e) => setPeriod1({ ...period1, startDate: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                    <input
                                        type="date"
                                        className="w-full border border-gray-300 rounded-md px-3 py-2"
                                        value={period1.endDate}
                                        onChange={(e) => setPeriod1({ ...period1, endDate: e.target.value })}
                                    />
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 className="font-medium mb-2">Period 2</h4>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                    <input
                                        type="date"
                                        className="w-full border border-gray-300 rounded-md px-3 py-2"
                                        value={period2.startDate}
                                        onChange={(e) => setPeriod2({ ...period2, startDate: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                    <input
                                        type="date"
                                        className="w-full border border-gray-300 rounded-md px-3 py-2"
                                        value={period2.endDate}
                                        onChange={(e) => setPeriod2({ ...period2, endDate: e.target.value })}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Period Type</label>
                            <select
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                value={comparisonPeriodType}
                                onChange={(e) => setComparisonPeriodType(e.target.value as PeriodType)}
                            >
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Group By</label>
                            <select
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                value={groupBy}
                                onChange={(e) => setGroupBy(e.target.value as 'rubro' | 'category')}
                            >
                                <option value="rubro">Item</option>
                                <option value="category">Category</option>
                            </select>
                        </div>
                        <div className="flex items-end">
                            <button
                                className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                                onClick={compareExpensesPeriods}
                                disabled={loading}
                            >
                                {loading ? 'Loading...' : 'Compare'}
                            </button>
                        </div>
                    </div>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {error}
                    </div>
                )}

                {comparisonData.length > 0 && (
                    <div className="space-y-6">
                        <div className="bg-white rounded-lg shadow p-4">
                            <h3 className="text-lg font-medium mb-4">Comparison Summary</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 className="font-medium mb-2">Period Totals</h4>
                                    <p className="mb-1">
                                        <span className="font-medium">Period 1 ({period1Label}):</span> {formatCurrency(periodTotals.period1)}
                                    </p>
                                    <p className="mb-1">
                                        <span className="font-medium">Period 2 ({period2Label}):</span> {formatCurrency(periodTotals.period2)}
                                    </p>
                                    <p className="mb-1">
                                        <span className="font-medium">Difference:</span> {formatCurrency(periodTotals.period2 - periodTotals.period1)}
                                        {" "}
                                        ({periodTotals.period1 > 0
                                            ? ((periodTotals.period2 - periodTotals.period1) / periodTotals.period1 * 100).toFixed(1)
                                            : "N/A"}%)
                                    </p>
                                </div>
                                <div className="h-48">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            data={[
                                                { name: period1Label, valor: periodTotals.period1 },
                                                { name: period2Label, valor: periodTotals.period2 }
                                            ]}
                                            margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                                        >
                                            <XAxis dataKey="name" />
                                            <YAxis />
                                            <Tooltip formatter={(value) => formatCurrency(value as number)} />
                                            <Bar dataKey="valor" fill="#8884d8" />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-4">
                            <h3 className="text-lg font-medium mb-4">Comparison by {groupBy === 'rubro' ? 'Item' : 'Category'}</h3>

                            <div className="mb-6">
                                <div className="h-64">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            data={comparisonData.slice(0, 10)}
                                            margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                                        >
                                            <XAxis dataKey="group_name" />
                                            <YAxis />
                                            <Tooltip formatter={(value) => formatCurrency(value as number)} />
                                            <Legend />
                                            <Bar dataKey="period1_amount" name={period1Label} fill="#8884d8" />
                                            <Bar dataKey="period2_amount" name={period2Label} fill="#82ca9d" />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>

                            <div className="overflow-x-auto overflow-y-auto" style={{ maxHeight: '300px' }}>
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {groupBy === 'rubro' ? 'Item' : 'Category'}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {period1Label}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {period2Label}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change %</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {comparisonData.map((item, idx) => (
                                            <tr key={idx}>
                                                <td className="px-6 py-4 whitespace-nowrap">{item.group_name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{formatCurrency(item.period1_amount)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{formatCurrency(item.period2_amount)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            ${item.difference > 0
                                                            ? 'bg-red-100 text-red-800'
                                                            : item.difference < 0
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-gray-100 text-gray-800'}`}>
                                                        {formatCurrency(item.difference)}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {item.difference_percentage !== null
                                                        ? <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                ${item.difference_percentage > 0
                                                                ? 'bg-red-100 text-red-800'
                                                                : item.difference_percentage < 0
                                                                    ? 'bg-green-100 text-green-800'
                                                                    : 'bg-gray-100 text-gray-800'}`}>
                                                            {item.difference_percentage > 0 ? '+' : ''}{item.difference_percentage.toFixed(1)}%
                                                        </span>
                                                        : 'N/A'
                                                    }
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        );
    };

    return (
        <DashboardLayout>
            <div className="bg-white rounded-lg shadow-lg p-4 md:p-6 animate-fade-in">
                <h2 className="text-2xl font-bold mb-6 text-gray-800">Expense Analysis Dashboard</h2>

                <div className="mb-6">
                    <div className="border-b border-gray-200">
                        <nav className="-mb-px flex">
                            <button
                                className={`py-4 px-6 font-medium text-sm ${activeTab === 'incidence'
                                        ? 'border-b-2 border-blue-500 text-blue-600'
                                        : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                onClick={() => handleTabChange('incidence')}
                            >
                                Incidence Analysis
                            </button>
                            <button
                                className={`py-4 px-6 font-medium text-sm ${activeTab === 'comparison'
                                        ? 'border-b-2 border-blue-500 text-blue-600'
                                        : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                onClick={() => handleTabChange('comparison')}
                            >
                                Period Comparison
                            </button>
                        </nav>
                    </div>
                </div>

                {activeTab === 'incidence' ? renderIncidenceTab() : renderComparisonTab()}
            </div>
        </DashboardLayout>
    );
};

export default Gastos;