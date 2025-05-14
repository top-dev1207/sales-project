import React, { useState } from 'react';
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ComposedChart,
  Area,
  TooltipProps
} from 'recharts';

// Define types for our data
interface MonthData {
  name: string;
  date: Date;
  [key: string]: any; // For dynamically generated property names
}

interface WeekData extends MonthData {
  month: string;
}

interface CustomTooltipProps extends TooltipProps<any, any> {
  selectedProvider: string;
}

// Sample data - providers
const proveedores: string[] = ["Proveedor A", "Proveedor B", "Proveedor C", "Proveedor D"];

// Generate sample data for 12 months
const generateSampleData = (): MonthData[] => {
  const data: MonthData[] = [];

  for (let month = 0; month < 12; month++) {
    const monthData: MonthData = {
      name: new Date(2024, month, 1).toLocaleDateString('es', { month: 'short', year: 'numeric' }),
      date: new Date(2024, month, 1),
    };

    // Add debt and payment data for each provider
    proveedores.forEach(proveedor => {
      // Generate debt that starts higher and tends to decrease
      const baseDebt = 10000 - (month * 500) + Math.random() * 1000;
      const debt = Math.max(0, baseDebt);

      // Payments increase over time
      const payment = 2000 + (month * 300) + Math.random() * 500;

      monthData[`deuda_${proveedor}`] = Math.round(debt);
      monthData[`pago_${proveedor}`] = Math.round(payment);
    });

    data.push(monthData);
  }

  return data;
};

// Weekly data - more detailed
const generateWeeklyData = (): WeekData[] => {
  const data: WeekData[] = [];

  for (let week = 0; week < 24; week++) {
    const date = new Date(2024, 0, 1 + week * 7);
    const weekData: WeekData = {
      name: `Sem ${week + 1}`,
      date: date,
      month: date.toLocaleDateString('es', { month: 'short' })
    };

    proveedores.forEach(proveedor => {
      // More fluctuation in weekly data
      const baseDebt = 10000 - (week * 250) + Math.random() * 1500;
      const debt = Math.max(0, baseDebt);

      const payment = 1000 + (week * 100) + Math.random() * 800;

      weekData[`deuda_${proveedor}`] = Math.round(debt);
      weekData[`pago_${proveedor}`] = Math.round(payment);
    });

    data.push(weekData);
  }

  return data;
};

// Calculate summary totals for the debt table
const calculateDebtSummary = (data: MonthData[] | WeekData[]) => {
  // Get the most recent data point (last item in the array)
  const latestData = data[data.length - 1];

  // Calculate total debt across all providers
  let totalDebt = 0;
  let totalInvoices = 0;

  proveedores.forEach(proveedor => {
    totalDebt += latestData[`deuda_${proveedor}`] || 0;
    // For simplicity, use the payment as a representation of invoices
    totalInvoices += latestData[`pago_${proveedor}`] || 0;
  });

  return {
    totalDebt,
    totalInvoices
  };
};

const monthlyData: MonthData[] = generateSampleData();
const weeklyData: WeekData[] = generateWeeklyData();

// Custom tooltip to display both debt and payment for the selected provider
const CustomTooltip: React.FC<CustomTooltipProps> = ({ active, payload, label, selectedProvider }) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-white p-4 shadow-md rounded border border-gray-200">
        <p className="font-bold">{label}</p>
        {payload.map((entry, index) => {
          // Only show data for the selected provider
          if (typeof entry.dataKey === 'string' && entry.dataKey.includes(selectedProvider)) {
            const isDebt = entry.dataKey.includes('deuda');
            return (
              <p key={index} style={{ color: entry.color }}>
                {isDebt ? 'Deuda: ' : 'Pago: '}
                {new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(entry.value)}
              </p>
            );
          }
          return null;
        })}
      </div>
    );
  }
  return null;
};

// Define types for component props and state
type ChartType = 'composed' | 'bar' | 'line';
type DataType = 'monthly' | 'weekly';

const ProveedorDebtPaymentChart: React.FC = () => {
  const [dataType, setDataType] = useState<DataType>('monthly');
  const [selectedProvider, setSelectedProvider] = useState<string>(proveedores[0]);
  const [chartType, setChartType] = useState<ChartType>('composed');

  const data: MonthData[] | WeekData[] = dataType === 'monthly' ? monthlyData : weeklyData;
  const debtSummary = calculateDebtSummary(data);

  // Colors for visualization
  const debtColor: string = "#ff6b6b";
  const paymentColor: string = "#4ecdc4";

  // Format currency
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
  };

  return (
    <div className="container mx-auto p-4 mt-[20px] shadow">
      <h2 className="text-2xl font-bold mb-4 text-center">Histórico de Deuda y Pagos a Proveedores</h2>

      {/* Controls */}
      <div className="flex flex-wrap gap-4 mb-6 justify-center">
        <div>
          <label className="font-medium">Periodo:</label>
          <div className="flex gap-2 mt-1">
            <button
              onClick={() => setDataType('monthly')}
              className={`px-3 py-1 rounded ${dataType === 'monthly' ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
            >
              Mensual
            </button>
            <button
              onClick={() => setDataType('weekly')}
              className={`px-3 py-1 rounded ${dataType === 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
            >
              Semanal
            </button>
          </div>
        </div>

        <div>
          <label className="font-medium">Proveedor:</label>
          <select
            value={selectedProvider}
            onChange={(e: React.ChangeEvent<HTMLSelectElement>) => setSelectedProvider(e.target.value)}
            className="border rounded px-2 py-1 mt-1"
          >
            {proveedores.map(proveedor => (
              <option key={proveedor} value={proveedor}>{proveedor}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="font-medium">Tipo de gráfico:</label>
          <div className="flex gap-2 mt-1">
            <button
              onClick={() => setChartType('composed')}
              className={`px-3 py-1 rounded ${chartType === 'composed' ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
            >
              Combinado
            </button>
            <button
              onClick={() => setChartType('bar')}
              className={`px-3 py-1 rounded ${chartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
            >
              Barras
            </button>
            <button
              onClick={() => setChartType('line')}
              className={`px-3 py-1 rounded ${chartType === 'line' ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
            >
              Líneas
            </button>
          </div>
        </div>
      </div>

      {/* Chart */}
      <div className="h-96 w-full">
        <ResponsiveContainer width="100%" height="100%">
          {chartType === 'composed' ? (
            <ComposedChart
              data={data}
              margin={{ top: 10, right: 30, left: 30, bottom: 40 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis
                dataKey="name"
                angle={0}
                textAnchor="middle"
                height={70}
                interval={dataType === 'weekly' ? 1 : 0}
              />
              <YAxis
                yAxisId="left"
                label={{ value: 'Monto ($)', angle: -90, position: 'insideLeft' }}
                tickFormatter={(value: number) => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(value)}
              />
              <Tooltip content={<CustomTooltip selectedProvider={selectedProvider} />} />
              <Legend />
              <Area
                type="monotone"
                dataKey={`deuda_${selectedProvider}`}
                name={`Deuda ${selectedProvider}`}
                fill={debtColor}
                stroke={debtColor}
                yAxisId="left"
              />
              <Bar
                dataKey={`pago_${selectedProvider}`}
                name={`Pagos ${selectedProvider}`}
                fill={paymentColor}
                yAxisId="left"
              />
            </ComposedChart>
          ) : chartType === 'bar' ? (
            <BarChart
              data={data}
              margin={{ top: 10, right: 30, left: 30, bottom: 40 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis
                dataKey="name"
                angle={0}
                textAnchor="middle"
                height={70}
                interval={dataType === 'weekly' ? 1 : 0}
              />
              <YAxis
                tickFormatter={(value: number) => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(value)}
                label={{ value: 'Monto ($)', angle: -90, position: 'insideLeft' }}
              />
              <Tooltip content={<CustomTooltip selectedProvider={selectedProvider} />} />
              <Legend />
              <Bar
                dataKey={`deuda_${selectedProvider}`}
                name={`Deuda ${selectedProvider}`}
                fill={debtColor}
              />
              <Bar
                dataKey={`pago_${selectedProvider}`}
                name={`Pagos ${selectedProvider}`}
                fill={paymentColor}
              />
            </BarChart>
          ) : (
            <LineChart
              data={data}
              margin={{ top: 10, right: 30, left: 30, bottom: 40 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis
                dataKey="name"
                angle={0}
                textAnchor="middle"
                height={70}
                interval={dataType === 'weekly' ? 1 : 0}
              />
              <YAxis
                tickFormatter={(value: number) => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(value)}
                label={{ value: 'Monto ($)', angle: -90, position: 'insideLeft' }}
              />
              <Tooltip content={<CustomTooltip selectedProvider={selectedProvider} />} />
              <Legend />
              <Line
                type="monotone"
                dataKey={`deuda_${selectedProvider}`}
                name={`Deuda ${selectedProvider}`}
                stroke={debtColor}
                dot={false}
                activeDot={{ r: 8 }}
                strokeWidth={2}
              />
              <Line
                type="monotone"
                dataKey={`pago_${selectedProvider}`}
                name={`Pagos ${selectedProvider}`}
                stroke={paymentColor}
                dot={false}
                activeDot={{ r: 8 }}
                strokeWidth={2}
              />
            </LineChart>
          )}
        </ResponsiveContainer>
      </div>

      {/* Simplified Summary Table - only showing values */}
      <div className="mt-8 mb-4">
        <h3 className="text-xl font-bold mb-4">Resumen</h3>

        {/* Current values table (simplified) */}
        <div className="mt-4 overflow-x-auto">
          <table className="w-full border-collapse">
            <thead>
              <tr>
                <th className="border border-blue-600 bg-blue-50 p-2 text-left">Métricas actuales</th>
                <th className="border border-blue-600 bg-blue-50 p-2 text-left">Valor</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td className="border border-gray-300 p-2">Deuda total de proveedores</td>
                <td className="border border-gray-300 p-2 font-medium">{formatCurrency(debtSummary.totalDebt)}</td>
              </tr>
              <tr>
                <td className="border border-gray-300 p-2">Pasivo de facturas</td>
                <td className="border border-gray-300 p-2 font-medium">{formatCurrency(debtSummary.totalInvoices)}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default ProveedorDebtPaymentChart;
