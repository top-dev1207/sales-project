import React, { useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, Legend, BarChart, Bar, XAxis, YAxis, CartesianGrid, ResponsiveContainer } from 'recharts';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

// Define types for the expense data
interface ExpenseItem {
    category: string;
    amount: number;
    percentage: number;
}

interface ExpenseData {
    daily: ExpenseItem[];
    weekly: ExpenseItem[];
    monthly: ExpenseItem[];
}

// Type for the timeFrame state
type TimeFrame = 'daily' | 'weekly' | 'monthly';

// Type for the chartType state
type ChartType = 'pie' | 'bar';

// Type for custom tooltip props
interface CustomTooltipProps {
    active?: boolean;
    payload?: any[];
}

// Sample expense data - replace with your actual data
const sampleData: ExpenseData = {
    daily: [
        { category: 'Food', amount: 25, percentage: 35 },
        { category: 'Transport', amount: 15, percentage: 21 },
        { category: 'Entertainment', amount: 10, percentage: 14 },
        { category: 'Utilities', amount: 5, percentage: 7 },
        { category: 'Miscellaneous', amount: 16, percentage: 23 }
    ],
    weekly: [
        { category: 'Food', amount: 175, percentage: 30 },
        { category: 'Transport', amount: 105, percentage: 18 },
        { category: 'Entertainment', amount: 70, percentage: 12 },
        { category: 'Utilities', amount: 120, percentage: 21 },
        { category: 'Rent', amount: 95, percentage: 16 },
        { category: 'Miscellaneous', amount: 17, percentage: 3 }
    ],
    monthly: [
        { category: 'Food', amount: 750, percentage: 25 },
        { category: 'Transport', amount: 450, percentage: 15 },
        { category: 'Entertainment', amount: 300, percentage: 10 },
        { category: 'Utilities', amount: 500, percentage: 17 },
        { category: 'Rent', amount: 900, percentage: 30 },
        { category: 'Miscellaneous', amount: 90, percentage: 3 }
    ]
};

// Color palette for chart segments
const COLORS: string[] = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D'];

const ExpensesChart: React.FC = () => {
    const [timeFrame, setTimeFrame] = useState<TimeFrame>('monthly');
    const [chartType, setChartType] = useState<ChartType>('pie');

    const handleTimeFrameChange = (e: React.ChangeEvent<HTMLSelectElement>): void => {
        setTimeFrame(e.target.value as TimeFrame);
    };

    const handleChartTypeChange = (e: React.ChangeEvent<HTMLSelectElement>): void => {
        setChartType(e.target.value as ChartType);
    };

    const currentData: ExpenseItem[] = sampleData[timeFrame];

    // Custom tooltip for pie chart
    const CustomTooltip: React.FC<CustomTooltipProps> = ({ active, payload }) => {
        if (active && payload && payload.length) {
            const data = payload[0].payload as ExpenseItem;
            return (
                <div className="p-4 border border-gray-200 rounded shadow-md">
                    <p className="font-bold">{data.category}</p>
                    <p className="text-sm">${data.amount}</p>
                    <p className="text-sm font-medium">{data.percentage}% of {timeFrame} expenses</p>
                </div>
            );
        }
        return null;
    };

    return (
        <DashboardLayout>
            <div className="w-full p-6 rounded-lg shadow-md">
                <h2 className="text-2xl font-bold mb-6 text-center text-gray-800">Expenses Breakdown by Percentage</h2>

                <div className="flex flex-wrap justify-center gap-4 mb-6">
                    <div className="flex items-center">
                        <label className="mr-2 font-medium text-gray-700">Time Period:</label>
                        <select
                            value={timeFrame}
                            onChange={handleTimeFrameChange}
                            className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div className="flex items-center">
                        <label className="mr-2 font-medium text-gray-700">Chart Type:</label>
                        <select
                            value={chartType}
                            onChange={handleChartTypeChange}
                            className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="pie">Pie Chart</option>
                            <option value="bar">Bar Chart</option>
                        </select>
                    </div>
                </div>

                <div className="h-96 w-full">
                    <ResponsiveContainer width="100%" height="100%">
                        {chartType === 'pie' ? (
                            <PieChart>
                                <Pie
                                    data={currentData}
                                    cx="50%"
                                    cy="50%"
                                    labelLine={true}
                                    outerRadius={130}
                                    fill="#8884d8"
                                    dataKey="amount"
                                    nameKey="category"
                                    label={({ name, percent }: { name: string; percent: number }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                >
                                    {currentData.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip content={<CustomTooltip />} />
                                <Legend layout="vertical" verticalAlign="middle" align="right" />
                            </PieChart>
                        ) : (
                            <BarChart
                                data={currentData}
                                margin={{
                                    top: 5,
                                    right: 30,
                                    left: 20,
                                    bottom: 5,
                                }}
                            >
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="category" />
                                <YAxis yAxisId="left" orientation="left" stroke="#8884d8" />
                                <YAxis yAxisId="right" orientation="right" stroke="#82ca9d" />
                                <Tooltip />
                                <Legend />
                                <Bar yAxisId="left" dataKey="amount" name="Amount ($)" fill="#8884d8" />
                                <Bar yAxisId="right" dataKey="percentage" name="Percentage (%)" fill="#82ca9d" />
                            </BarChart>
                        )}
                    </ResponsiveContainer>
                </div>

                <div className="mt-8">
                    <h3 className="text-xl font-semibold mb-4">Expense Breakdown Details</h3>
                    <div className="overflow-x-auto">
                        <table className="min-w-full border border-gray-200">
                            <thead>
                                <tr>
                                    <th className="py-2 px-4 border-b text-left">Category</th>
                                    <th className="py-2 px-4 border-b text-right">Amount ($)</th>
                                    <th className="py-2 px-4 border-b text-right">Percentage (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                {currentData.map((item, index) => (
                                    <tr key={index}>
                                        <td className="py-2 px-4 border-b">
                                            <div className="flex items-center">
                                                <div className="w-3 h-3 rounded-full mr-2" style={{ backgroundColor: COLORS[index % COLORS.length] }}></div>
                                                {item.category}
                                            </div>
                                        </td>
                                        <td className="py-2 px-4 border-b text-right">${item.amount}</td>
                                        <td className="py-2 px-4 border-b text-right">{item.percentage}%</td>
                                    </tr>
                                ))}
                                <tr className="font-bold">
                                    <td className="py-2 px-4 border-b">Total</td>
                                    <td className="py-2 px-4 border-b text-right">
                                        ${currentData.reduce((sum, item) => sum + item.amount, 0)}
                                    </td>
                                    <td className="py-2 px-4 border-b text-right">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default ExpensesChart;
