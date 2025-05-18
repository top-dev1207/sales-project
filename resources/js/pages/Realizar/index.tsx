import React, { useState } from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import { useToast } from "@/hooks/use-toast";
import { BarChart, Bar, LineChart, Line, PieChart, Pie, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, Cell } from 'recharts';
import SalesWeatherChart from './SalesWeatherChart';
import SalaryDashboard from './SalaryDashboard';
import Proveedores from './Proveedores';

const Realizar = () => {
    // Sample data - this would be replaced with real data in a production environment
    const [selectedLocation, setSelectedLocation] = useState('Local 1');
    const [selectedPeriod, setSelectedPeriod] = useState('Mayo 2025');
    const [compareMode, setCompareMode] = useState(false);
    const [secondaryPeriod, setSecondaryPeriod] = useState('Abril 2025');

    // Sample locations and periods for filters
    const locations = ['Local 1', 'Local 2', 'Local 3'];
    const periods = ['Mayo 2025', 'Abril 2025', 'Marzo 2025', 'Febrero 2025'];

    // Sales data
    const salesProjection = {
        monthly: 5000000,
        annual: 60000000,
        achieved: 2750000,
        objective: 5000000,
        percentAchieved: 55
    };

    // Debt data
    const debtData = {
        totalDebt: 1250000,
        currentLiabilities: 850000
    };

    // Weekly debt progression data
    const debtProgressionData = [
        { week: 'Semana 1', debt: 1500000 },
        { week: 'Semana 2', debt: 1400000 },
        { week: 'Semana 3', debt: 1350000 },
        { week: 'Semana 4', debt: 1250000 }
    ];

    // Top expenses data
    const topExpensesData = [
        { name: 'Insumos', amount: 450000 },
        { name: 'Personal', amount: 350000 },
        { name: 'Alquiler', amount: 200000 },
        { name: 'Servicios', amount: 150000 },
        { name: 'Marketing', amount: 100000 }
    ];

    // Cost metrics
    const costMetrics = {
        goodCost: 350000,
        beverageCost: 200000,
        mixCost: 450000,
        expensesOverSales: 42,
        expensesDistribution: 38
    };


    // Sales objective dynamic
    const salesObjectiveData = {
        totalObjective: 5000000,
        currentAchieved: 2750000,
        remainingDays: 12,
        dailyProjection: 187500
    };

    // Profit data
    const profitData = {
        netProfit: 850000,
        profitMargin: 30.9
    };

    // Daily sales trend
    const dailySalesTrend = [
        { day: '1', sales: 125000 },
        { day: '2', sales: 145000 },
        { day: '3', sales: 115000 },
        { day: '4', sales: 160000 },
        { day: '5', sales: 180000 },
        { day: '6', sales: 140000 },
        { day: '7', sales: 120000 },
        { day: '8', sales: 130000 },
        { day: '9', sales: 150000 },
        { day: '10', sales: 175000 },
        { day: '11', sales: 160000 },
        { day: '12', sales: 135000 },
        { day: '13', sales: 145000 },
        { day: '14', sales: 155000 },
        { day: '15', sales: 170000 },
        { day: '16', sales: 180000 },
        { day: '17', sales: 165000 },
        { day: '18', sales: 150000 }
    ];

    // Chart colors
    const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#A28BFF'];

    return (
        <DashboardLayout>
            <div className="flex flex-col p-4 bg-gray-50 text-gray-800">
                <h1 className="text-2xl font-bold mb-4">Panel de KPIs</h1>
                {/* Filters Section */}
                <div className="flex flex-wrap gap-4 mb-6 p-4 rounded-lg shadow">
                    <div>
                        <label className="block text-sm font-medium mb-1">Local:</label>
                        <select
                            className="border border-gray-300 rounded-md p-2"
                            value={selectedLocation}
                            onChange={(e) => setSelectedLocation(e.target.value)}
                        >
                            {locations.map(loc => (
                                <option key={loc} value={loc}>{loc}</option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium mb-1">Período:</label>
                        <select
                            className="border border-gray-300 rounded-md p-2"
                            value={selectedPeriod}
                            onChange={(e) => setSelectedPeriod(e.target.value)}
                        >
                            {periods.map(period => (
                                <option key={period} value={period}>{period}</option>
                            ))}
                        </select>
                    </div>
                    <div className="flex items-end">
                        <label className="inline-flex items-center">
                            <input
                                type="checkbox"
                                className="form-checkbox"
                                checked={compareMode}
                                onChange={() => setCompareMode(!compareMode)}
                            />
                            <span className="ml-2">Comparar con otro período</span>
                        </label>
                    </div>
                    {compareMode && (
                        <div>
                            <label className="block text-sm font-medium mb-1">Período comparativo:</label>
                            <select
                                className="border border-gray-300 rounded-md p-2"
                                value={secondaryPeriod}
                                onChange={(e) => setSecondaryPeriod(e.target.value)}
                            >
                                {periods.filter(p => p !== selectedPeriod).map(period => (
                                    <option key={period} value={period}>{period}</option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                {/* Main Dashboard Grid */}
                {/* <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> */}

                {/* Sales Projection Card */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="p-4 rounded-lg shadow">
                        <h2 className="text-lg font-semibold mb-3">Proyección de Ventas</h2>
                        <div className="flex justify-between mb-2">
                            <span>Proyección mensual:</span>
                            <span className="font-medium">${salesProjection.monthly.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between mb-2">
                            <span>Proyección anual:</span>
                            <span className="font-medium">${salesProjection.annual.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between mb-4">
                            <span>% Realización:</span>
                            <span className="font-medium">{salesProjection.percentAchieved}%</span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2.5">
                            <div
                                className="bg-blue-600 h-2.5 rounded-full"
                                style={{ width: `${salesProjection.percentAchieved}%` }}
                            ></div>
                        </div>
                        <div className="mt-2 text-sm text-gray-500 text-right">
                            ${salesProjection.achieved.toLocaleString()} de ${salesProjection.objective.toLocaleString()}
                        </div>
                    </div>

                    {/* Sales Dynamic Card */}
                    <div className="p-4 rounded-lg shadow">
                        <h2 className="text-lg font-semibold mb-3">Dinámica de %Ventas/Objetivo</h2>
                        <div className="mb-3">
                            <div className="flex justify-between mb-1">
                                <span>Objetivo mensual:</span>
                                <span className="font-medium">${salesObjectiveData.totalObjective.toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between mb-1">
                                <span>Venta actual:</span>
                                <span className="font-medium">${salesObjectiveData.currentAchieved.toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between mb-1">
                                <span>Días restantes:</span>
                                <span className="font-medium">{salesObjectiveData.remainingDays}</span>
                            </div>
                            <div className="flex justify-between mb-1 text-green-600 font-medium">
                                <span>Venta diaria proyectada:</span>
                                <span>${salesObjectiveData.dailyProjection.toLocaleString()}</span>
                            </div>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                            <div
                                className="bg-green-500 h-2.5 rounded-full"
                                style={{ width: `${(salesObjectiveData.currentAchieved / salesObjectiveData.totalObjective) * 100}%` }}
                            ></div>
                        </div>
                        <div className="text-sm text-gray-500 text-right">
                            {((salesObjectiveData.currentAchieved / salesObjectiveData.totalObjective) * 100).toFixed(1)}% completado
                        </div>
                    </div>
                </div>
                {/* <Proveedores /> */}
                {/* Top Expenses Card */}
                <div className="p-4 rounded-lg shadow">
                    <h2 className="text-lg font-semibold mb-3">Gastos más Relevantes</h2>
                    <div className="h-64">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart
                                data={topExpensesData}
                                layout="vertical"
                                margin={{ top: 5, right: 5, left: 25, bottom: 5 }}
                            >
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis type="number" />
                                <YAxis dataKey="name" type="category" />
                                <Tooltip formatter={(value) => [`$${value.toLocaleString()}`, 'Monto']} />
                                <Bar dataKey="amount" fill="#8884d8">
                                    {topExpensesData.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Cost Metrics Card */}
                <div className="p-4 rounded-lg shadow">
                    <h2 className="text-lg font-semibold mb-3">Métricas de Costo</h2>
                    <div className="flex justify-between mb-2">
                        <span>Good Cost:</span>
                        <span className="font-medium">${costMetrics.goodCost.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between mb-2">
                        <span>Beverage Cost:</span>
                        <span className="font-medium">${costMetrics.beverageCost.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between mb-2">
                        <span>Mix Cost:</span>
                        <span className="font-medium">${costMetrics.mixCost.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between mb-2">
                        <span>Gasto sobre ventas:</span>
                        <span className="font-medium">{costMetrics.expensesOverSales}%</span>
                    </div>
                    <div className="flex justify-between mb-2">
                        <span>Gastos sobre total:</span>
                        <span className="font-medium">{costMetrics.expensesDistribution}%</span>
                    </div>
                </div>

                {/* Profit Card */}
                <div className="p-4 rounded-lg shadow">
                    <h2 className="text-lg font-semibold mb-3">Ganancias</h2>
                    <div className="flex justify-between mb-2">
                        <span>Ganancia Neta:</span>
                        <span className="font-medium">${profitData.netProfit.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between mb-4">
                        <span>Margen de Ganancia:</span>
                        <span className="font-medium">{profitData.profitMargin}%</span>
                    </div>
                    <div className="h-40">
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <Pie
                                    data={[
                                        { name: 'Ganancia', value: profitData.netProfit },
                                        { name: 'Costos', value: salesProjection.achieved - profitData.netProfit }
                                    ]}
                                    cx="50%"
                                    cy="50%"
                                    labelLine={false}
                                    outerRadius={80}
                                    fill="#8884d8"
                                    dataKey="value"
                                    label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                >
                                    {[0, 1].map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={index === 0 ? '#00C49F' : '#FF8042'} />
                                    ))}
                                </Pie>
                                <Tooltip formatter={(value) => [`$${value.toLocaleString()}`]} />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>
                <SalaryDashboard />
                {/* </div> */}
                <SalesWeatherChart />
            </div>
        </DashboardLayout>
    );
};

export default Realizar;
