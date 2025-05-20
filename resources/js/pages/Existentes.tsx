import { set } from 'date-fns';
import { useState, useEffect } from 'react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
// Constants
const MONTHS: string[] = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const COLORS: string[] = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#a05195', '#d45087', '#2f4b7c', '#665191'];

// Type definitions
interface MetricsData {
    ventas_totales: number[];
    total_gastos: number[];
    ganancia_bruta: number[];
    iibb: number[];
    iva: number[];
    impuesto_ganancia: number[];
    ganancia_neta: number[];
    ganancia_porcentaje: number[];
    costo_alimento_porcentaje: number[];
    costo_bebida_porcentaje: number[];
    costo_mixto_porcentaje: number[];
    ingresos_propietarios: number[];
    inversiones: number[];
    pago_deuda_atrasada: number[];
    retiro_dividendos: number[];
    gastos_cta_cte: number[];
    caja_final: number[];
}

interface TotalsData {
    ventas_totales: number;
    total_gastos: number;
    ganancia_bruta: number;
    iibb: number;
    iva: number;
    impuesto_ganancia: number;
    ganancia_neta: number;
    ingresos_propietarios: number;
    inversiones: number;
    pago_deuda_atrasada: number;
    retiro_dividendos: number;
    gastos_cta_cte: number;
    caja_final: number;
    ganancia_porcentaje: number;
    costo_alimento_porcentaje: number;
    costo_bebida_porcentaje: number;
    costo_mixto_porcentaje: number;
}

interface FinancialData {
    year: number;
    months: string[];
    metrics: MetricsData;
    totals: TotalsData;
}

interface ChartDataPoint {
    name: string;
    ventas: number;
    gastos: number;
    ganancia: number;
    caja: number;
}

interface EfficiencyDataPoint {
    name: string;
    ganancia: number;
    costoAlimento: number;
    costoBebida: number;
    costoMixto: number;
}

interface FlowsDataPoint {
    name: string;
    ingresos: number;
    inversiones: number;
    deuda: number;
    dividendos: number;
    ctaCte: number;
}

interface PieDataPoint {
    name: string;
    value: number;
}

// Formatter functions
const formatCurrency = (value: number | null | undefined): string => {
    if (value === null || value === undefined) return '-';
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

const formatPercentage = (value: number | null | undefined): string => {
    if (value === null || value === undefined) return '-';
    return `${value.toFixed(2)}%`;
};

const FinancialDashboard: React.FC = () => {
    const [data, setData] = useState<FinancialData | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string | null>(null);
    const [selectedYear, setSelectedYear] = useState<number>(new Date().getFullYear());
    const [availableYears, setAvailableYears] = useState<number[]>([2024, 2023, 2022]);

    useEffect(() => {
        const fetchData = async (): Promise<void> => {
            setLoading(true);
            try {
                // In a real app, this would be an actual API call
                const response = await fetch(`/api/financial-metrics/yearly?year=${selectedYear}`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const jsonData: FinancialData = await response.json();
                setData(jsonData);
                setLoading(false);
            } catch (err) {
                if (err instanceof Error) {
                    setError(err.message);
                } else {
                    setError('An unknown error occurred');
                }
                setLoading(false);
                // For demo purposes, let's create some mock data
                const mockData = generateMockData(selectedYear);
                setData(mockData);
            }
        };

        fetchData();
        getAvailableYears();
    }, [selectedYear]);
    const getAvailableYears = () => {
        const currentYear = new Date().getFullYear();
        const tempYears = [];
        for (let year = 2022; year <= currentYear; year++) {
            tempYears.push(year);
        }
        setAvailableYears(tempYears);
    };
    const generateMockData = (year: number): FinancialData => {
        // Generate random data for demonstration purposes
        const months = MONTHS;
        const salesByMonth = Array(12).fill(0).map(() => Math.random() * 1000000 + 500000);
        const expensesByMonth = Array(12).fill(0).map((_, i) => salesByMonth[i] * (Math.random() * 0.5 + 0.3));
        const grossProfitByMonth = salesByMonth.map((sale, i) => sale - expensesByMonth[i]);

        const taxData = {
            iibb: Array(12).fill(0).map((_, i) => grossProfitByMonth[i] * (Math.random() * 0.05 + 0.02)),
            iva: Array(12).fill(0).map((_, i) => grossProfitByMonth[i] * (Math.random() * 0.1 + 0.05)),
            impuesto_ganancia: Array(12).fill(0).map((_, i) => grossProfitByMonth[i] * (Math.random() * 0.15 + 0.05))
        };

        const netProfitByMonth = grossProfitByMonth.map((profit, i) =>
            profit - taxData.iibb[i] - taxData.iva[i] - taxData.impuesto_ganancia[i]);

        const additionalData = {
            ingresos_propietarios: Array(12).fill(0).map(() => Math.random() * 100000),
            inversiones: Array(12).fill(0).map(() => Math.random() * 200000),
            pago_deuda_atrasada: Array(12).fill(0).map(() => Math.random() * 50000),
            retiro_dividendos: Array(12).fill(0).map(() => Math.random() * 150000),
            gastos_cta_cte: Array(12).fill(0).map(() => Math.random() * 30000)
        };

        const finalCash = netProfitByMonth.map((profit, i) =>
            profit + additionalData.ingresos_propietarios[i] - additionalData.inversiones[i]
            - additionalData.pago_deuda_atrasada[i] - additionalData.retiro_dividendos[i]
            - additionalData.gastos_cta_cte[i]);

        const totals: TotalsData = {
            ventas_totales: salesByMonth.reduce((a, b) => a + b, 0),
            total_gastos: expensesByMonth.reduce((a, b) => a + b, 0),
            ganancia_bruta: grossProfitByMonth.reduce((a, b) => a + b, 0),
            iibb: taxData.iibb.reduce((a, b) => a + b, 0),
            iva: taxData.iva.reduce((a, b) => a + b, 0),
            impuesto_ganancia: taxData.impuesto_ganancia.reduce((a, b) => a + b, 0),
            ganancia_neta: netProfitByMonth.reduce((a, b) => a + b, 0),
            ingresos_propietarios: additionalData.ingresos_propietarios.reduce((a, b) => a + b, 0),
            inversiones: additionalData.inversiones.reduce((a, b) => a + b, 0),
            pago_deuda_atrasada: additionalData.pago_deuda_atrasada.reduce((a, b) => a + b, 0),
            retiro_dividendos: additionalData.retiro_dividendos.reduce((a, b) => a + b, 0),
            gastos_cta_cte: additionalData.gastos_cta_cte.reduce((a, b) => a + b, 0),
            caja_final: finalCash.reduce((a, b) => a + b, 0),
            ganancia_porcentaje: 18.45,
            costo_alimento_porcentaje: 22.34,
            costo_bebida_porcentaje: 24.12,
            costo_mixto_porcentaje: 23.56
        };

        const metrics: MetricsData = {
            ventas_totales: salesByMonth,
            total_gastos: expensesByMonth,
            ganancia_bruta: grossProfitByMonth,
            iibb: taxData.iibb,
            iva: taxData.iva,
            impuesto_ganancia: taxData.impuesto_ganancia,
            ganancia_neta: netProfitByMonth,
            ganancia_porcentaje: Array(12).fill(0).map((_, i) => salesByMonth[i] > 0 ? (netProfitByMonth[i] / salesByMonth[i]) * 100 : 0),
            costo_alimento_porcentaje: Array(12).fill(0).map(() => Math.random() * 10 + 15),
            costo_bebida_porcentaje: Array(12).fill(0).map(() => Math.random() * 10 + 15),
            costo_mixto_porcentaje: Array(12).fill(0).map(() => Math.random() * 10 + 15),
            ingresos_propietarios: additionalData.ingresos_propietarios,
            inversiones: additionalData.inversiones,
            pago_deuda_atrasada: additionalData.pago_deuda_atrasada,
            retiro_dividendos: additionalData.retiro_dividendos,
            gastos_cta_cte: additionalData.gastos_cta_cte,
            caja_final: finalCash
        };

        return {
            year: year,
            months: months,
            metrics: metrics,
            totals: totals
        };
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
            </div>
        );
    }

    if (error && !data) {
        return (
            <div className="flex items-center justify-center h-screen">
                <div className="text-red-500 text-xl">
                    Error: {error}
                </div>
            </div>
        );
    }

    if (!data) {
        return null; // Early return if data is not available
    }

    // Prepare chart data
    const chartData: ChartDataPoint[] = MONTHS.map((month, index) => ({
        name: month,
        ventas: data.metrics.ventas_totales[index],
        gastos: data.metrics.total_gastos[index],
        ganancia: data.metrics.ganancia_neta[index],
        caja: data.metrics.caja_final[index]
    }));

    // Prepare profit and costs data for pie chart
    const pieData: PieDataPoint[] = [
        { name: 'Ganancia Neta', value: data.totals.ganancia_neta },
        { name: 'Impuestos', value: data.totals.iibb + data.totals.iva + data.totals.impuesto_ganancia },
        { name: 'Inversiones', value: data.totals.inversiones },
        { name: 'Retiro Dividendos', value: data.totals.retiro_dividendos },
        { name: 'Otros Gastos', value: data.totals.gastos_cta_cte + data.totals.pago_deuda_atrasada }
    ].filter(item => item.value > 0);

    // Prepare efficiency percentages data
    const efficiencyData: EfficiencyDataPoint[] = MONTHS.map((month, index) => ({
        name: month,
        ganancia: data.metrics.ganancia_porcentaje[index],
        costoAlimento: data.metrics.costo_alimento_porcentaje[index],
        costoBebida: data.metrics.costo_bebida_porcentaje[index],
        costoMixto: data.metrics.costo_mixto_porcentaje[index]
    }));

    // Prepare financial flows data
    const flowsData: FlowsDataPoint[] = MONTHS.map((month, index) => ({
        name: month,
        ingresos: data.metrics.ingresos_propietarios[index],
        inversiones: -data.metrics.inversiones[index],
        deuda: -data.metrics.pago_deuda_atrasada[index],
        dividendos: -data.metrics.retiro_dividendos[index],
        ctaCte: -data.metrics.gastos_cta_cte[index]
    }));

    return (
        <DashboardLayout>
            <div className="container mx-auto p-4 bg-gray-50">
                <div className="bg-white shadow rounded-lg p-6 mb-8">
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-2xl font-bold text-gray-800">Dashboard Financiero {data.year}</h1>
                        <div className="flex space-x-2">
                            <select
                                onChange={(e) => setSelectedYear(Number(e.target.value))}
                                value={selectedYear}
                                className="px-4 py-2 rounded"
                            >
                                {availableYears.map((year) => (
                                    <option key={year} value={year}>
                                        {year}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Key metrics */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div className="bg-blue-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-blue-800">Ventas Totales</h3>
                            <p className="text-3xl font-bold text-blue-600">{formatCurrency(data.totals.ventas_totales)}</p>
                        </div>

                        <div className="bg-red-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-red-800">Gastos Totales</h3>
                            <p className="text-3xl font-bold text-red-600">{formatCurrency(data.totals.total_gastos)}</p>
                        </div>

                        <div className="bg-green-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-green-800">Ganancia Neta</h3>
                            <p className="text-3xl font-bold text-green-600">{formatCurrency(data.totals.ganancia_neta)}</p>
                        </div>

                        <div className="bg-purple-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-purple-800">Caja Final</h3>
                            <p className="text-3xl font-bold text-purple-600">{formatCurrency(data.totals.caja_final)}</p>
                        </div>
                    </div>

                    {/* Efficiency metrics */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div className="bg-yellow-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-yellow-800">Ganancia %</h3>
                            <p className="text-3xl font-bold text-yellow-600">{formatPercentage(data.totals.ganancia_porcentaje)}</p>
                        </div>

                        <div className="bg-orange-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-orange-800">Costo Alimento %</h3>
                            <p className="text-3xl font-bold text-orange-600">{formatPercentage(data.totals.costo_alimento_porcentaje)}</p>
                        </div>

                        <div className="bg-pink-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-pink-800">Costo Bebida %</h3>
                            <p className="text-3xl font-bold text-pink-600">{formatPercentage(data.totals.costo_bebida_porcentaje)}</p>
                        </div>

                        <div className="bg-indigo-50 p-4 rounded-lg shadow">
                            <h3 className="text-lg font-semibold text-indigo-800">Costo Mixto %</h3>
                            <p className="text-3xl font-bold text-indigo-600">{formatPercentage(data.totals.costo_mixto_porcentaje)}</p>
                        </div>
                    </div>

                    {/* Main Chart */}
                    <div className="mb-8">
                        <h2 className="text-xl font-bold text-gray-800 mb-4">Ventas, Gastos y Ganancias mensuales</h2>
                        <div className="h-96">
                            <ResponsiveContainer width="100%" height="100%">
                                <LineChart
                                    data={chartData}
                                    margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                                >
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip formatter={(value: number) => formatCurrency(value)} />
                                    <Legend />
                                    <Line type="monotone" dataKey="ventas" stroke="#3B82F6" activeDot={{ r: 8 }} name="Ventas" />
                                    <Line type="monotone" dataKey="gastos" stroke="#EF4444" name="Gastos" />
                                    <Line type="monotone" dataKey="ganancia" stroke="#10B981" name="Ganancia Neta" />
                                    <Line type="monotone" dataKey="caja" stroke="#8B5CF6" name="Caja Final" />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    {/* Two charts in a row */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        {/* Efficiency Chart */}
                        <div>
                            <h2 className="text-xl font-bold text-gray-800 mb-4">Porcentajes de Eficiencia</h2>
                            <div className="h-80">
                                <ResponsiveContainer width="100%" height="100%">
                                    <LineChart
                                        data={efficiencyData}
                                        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis dataKey="name" />
                                        <YAxis />
                                        <Tooltip formatter={(value: number) => `${value.toFixed(2)}%`} />
                                        <Legend />
                                        <Line type="monotone" dataKey="ganancia" stroke="#F59E0B" name="Ganancia %" />
                                        <Line type="monotone" dataKey="costoAlimento" stroke="#F97316" name="Costo Alimento %" />
                                        <Line type="monotone" dataKey="costoBebida" stroke="#EC4899" name="Costo Bebida %" />
                                        <Line type="monotone" dataKey="costoMixto" stroke="#6366F1" name="Costo Mixto %" />
                                    </LineChart>
                                </ResponsiveContainer>
                            </div>
                        </div>

                        {/* Distribution Pie Chart */}
                        <div>
                            <h2 className="text-xl font-bold text-gray-800 mb-4">Distribución de Fondos</h2>
                            <div className="h-80">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={pieData}
                                            cx="50%"
                                            cy="50%"
                                            labelLine={false}
                                            outerRadius={80}
                                            fill="#8884d8"
                                            dataKey="value"
                                            label={({ name, percent }: { name: string, percent: number }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                        >
                                            {pieData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                            ))}
                                        </Pie>
                                        <Tooltip formatter={(value: number) => formatCurrency(value)} />
                                        <Legend />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    </div>

                    {/* Financial Flows */}
                    <div>
                        <h2 className="text-xl font-bold text-gray-800 mb-4">Flujos Financieros Mensuales</h2>
                        <div className="h-80">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart
                                    data={flowsData}
                                    margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                                >
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip formatter={(value: number) => formatCurrency(value)} />
                                    <Legend />
                                    <Bar dataKey="ingresos" stackId="a" fill="#10B981" name="Ingresos Propietarios" />
                                    <Bar dataKey="inversiones" stackId="a" fill="#3B82F6" name="Inversiones" />
                                    <Bar dataKey="deuda" stackId="a" fill="#F59E0B" name="Pago Deuda" />
                                    <Bar dataKey="dividendos" stackId="a" fill="#EF4444" name="Retiro Dividendos" />
                                    <Bar dataKey="ctaCte" stackId="a" fill="#8B5CF6" name="Gastos Cta Cte" />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    {/* Detailed Table */}
                    <div className="mt-8">
                        <h2 className="text-xl font-bold text-gray-800 mb-4">Detalle Mensual</h2>
                        <div className="overflow-x-auto">
                            <table className="min-w-full bg-white">
                                <thead>
                                    <tr className="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                        <th className="py-3 px-6 text-left">Concepto</th>
                                        {MONTHS.map((month, index) => (
                                            <th key={index} className="py-3 px-6 text-center">{month}</th>
                                        ))}
                                        <th className="py-3 px-6 text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="text-gray-600 text-sm">
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Ventas Totales</td>
                                        {data.metrics.ventas_totales.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.ventas_totales)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Gastos Totales</td>
                                        {data.metrics.total_gastos.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.total_gastos)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100 bg-green-50">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Ganancia Bruta</td>
                                        {data.metrics.ganancia_bruta.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.ganancia_bruta)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">IIBB</td>
                                        {data.metrics.iibb.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.iibb)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">IVA</td>
                                        {data.metrics.iva.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.iva)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Impuesto Ganancias</td>
                                        {data.metrics.impuesto_ganancia.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.impuesto_ganancia)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100 bg-green-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Ganancia Neta</td>
                                        {data.metrics.ganancia_neta.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.ganancia_neta)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Ingresos Propietarios</td>
                                        {data.metrics.ingresos_propietarios.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.ingresos_propietarios)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Inversiones</td>
                                        {data.metrics.inversiones.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.inversiones)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Pago Deuda Atrasada</td>
                                        {data.metrics.pago_deuda_atrasada.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.pago_deuda_atrasada)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Retiro Dividendos</td>
                                        {data.metrics.retiro_dividendos.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.retiro_dividendos)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Gastos Cta. Cte.</td>
                                        {data.metrics.gastos_cta_cte.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.gastos_cta_cte)}</td>
                                    </tr>
                                    <tr className="border-b border-gray-200 hover:bg-gray-100 bg-purple-100">
                                        <td className="py-3 px-6 text-left whitespace-nowrap font-medium">Caja Final</td>
                                        {data.metrics.caja_final.map((value, index) => (
                                            <td key={index} className="py-3 px-6 text-center">{formatCurrency(value)}</td>
                                        ))}
                                        <td className="py-3 px-6 text-center font-bold">{formatCurrency(data.totals.caja_final)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div className="text-center text-sm text-gray-500 mt-8">
                    <p>Dashboard Financiero © {new Date().getFullYear()} - Todos los datos son actualizados automáticamente</p>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default FinancialDashboard;