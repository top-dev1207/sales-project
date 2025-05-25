import React, { useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, BarChart, Bar } from 'recharts';
import { Calendar, ChevronDown, Filter, RefreshCw } from 'lucide-react';
import DateInput from '@/components/ui/DateInput';

// Define types for our data
interface CostMetric {
  period: string;
  food_cost: number;
  beverage_cost: number;
  mix_cost: number;
  [key: string]: string | number; // For dynamic access
}

interface DetailedMetric {
  date: string;
  day_name: string;
  value: number;
  [key: string]: string | number; // For dynamic access
}

interface ApiResponse {
  status: string;
  data: CostMetric[] | DetailedMetric[];
  message?: string;
}

type TemporalityType = 'weekly' | 'monthly';
type MetricType = 'food' | 'beverage' | 'mix';

const CostMetricsPanel: React.FC = () => {
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [data, setData] = useState<CostMetric[]>([]);
  const [detailedData, setDetailedData] = useState<DetailedMetric[]>([]);
  const [temporality, setTemporality] = useState<TemporalityType>('monthly');
  const [metricType, setMetricType] = useState<MetricType>('mix');
  const [startDate, setStartDate] = useState<string>(() => {
    const date = new Date();
    date.setMonth(date.getMonth() - 3);
    return date.toISOString().split('T')[0];
  });
  const [endDate, setEndDate] = useState<string>(() => {
    const date = new Date();
    return date.toISOString().split('T')[0];
  });
  const [showFilters, setShowFilters] = useState<boolean>(false);

  const fetchCostMetrics = async (): Promise<void> => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`/api/cost-metrics?start_date=${startDate}&end_date=${endDate}&temporality=${temporality}`);
      if (!response.ok) {
        throw new Error('Error al obtener las métricas de costo');
      }
      const result = await response.json() as ApiResponse;
      if (result.status === 'success') {
        setData(result.data as CostMetric[]);
      } else {
        throw new Error(result.message || 'Error desconocido');
      }
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
        console.error('Error fetching cost metrics:', err);
      } else {
        setError('An unknown error occurred');
        console.error('Unknown error:', err);
      }
    } finally {
      setLoading(false);
    }
  };

  const fetchDetailedMetrics = async (): Promise<void> => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`/api/cost-metrics/detailed?start_date=${startDate}&end_date=${endDate}&metric_type=${metricType}`);
      if (!response.ok) {
        throw new Error('Error al obtener las métricas detalladas');
      }
      const result = await response.json() as ApiResponse;
      if (result.status === 'success') {
        setDetailedData(result.data as DetailedMetric[]);
      } else {
        throw new Error(result.message || 'Error desconocido');
      }
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
        console.error('Error fetching detailed metrics:', err);
      } else {
        setError('An unknown error occurred');
        console.error('Unknown error:', err);
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCostMetrics();
    fetchDetailedMetrics();
  }, []);

  const handleApplyFilters = (): void => {
    fetchCostMetrics();
    fetchDetailedMetrics();
    setShowFilters(false);
  };

  const formatPercentage = (value: number): string => {
    return `${value.toFixed(2)}%`;
  };

  const getChartColor = (metricType: string): string => {
    switch (metricType) {
      case 'food_cost':
        return '#10B981'; // Verde
      case 'beverage_cost':
        return '#3B82F6'; // Azul
      case 'mix_cost':
        return '#8B5CF6'; // Púrpura
      default:
        return '#6366F1'; // Indigo
    }
  };

  return (
    <div className="flex flex-col h-full bg-background dark:bg-card rounded-lg shadow-lg p-4 md:p-6 animate-fade-in shadow mt-[50px] transition-colors duration-200">
      {/* Header */}
      <div className="px-6 py-4 border-b border-border dark:border-border">
        <div className="flex justify-between items-center">
          <h2 className="text-2xl font-semibold text-foreground dark:text-foreground">Métricas de Costos</h2>
          <div className="flex items-center space-x-2">
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="flex items-center px-3 py-2 text-sm font-medium text-foreground dark:text-foreground bg-background dark:bg-card border border-border dark:border-border rounded-md hover:bg-secondary dark:hover:bg-sidebar-accent transition-colors duration-200"
            >
              <Filter className="w-4 h-4 mr-2" />
              Filtros
              <ChevronDown className={`w-4 h-4 ml-1 transform transition-transform duration-200 ${showFilters ? 'rotate-180' : ''}`} />
            </button>
            <button
              onClick={() => {
                fetchCostMetrics();
                fetchDetailedMetrics();
              }}
              className="flex items-center px-3 py-2 text-sm font-medium text-primary-foreground dark:text-primary-foreground bg-primary dark:bg-primary rounded-md hover:bg-primary/90 dark:hover:bg-primary/90 transition-colors duration-200"
            >
              <RefreshCw className="w-4 h-4 mr-1" />
              Actualizar
            </button>
          </div>
        </div>

        {/* Filters */}
        {showFilters && (
          <div className="mt-4 p-4 bg-secondary dark:bg-sidebar-background rounded-md border border-border dark:border-border transition-colors duration-200">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label className="block text-sm font-medium text-muted-foreground dark:text-muted-foreground mb-1">Desde</label>
                <div className="relative">
                  <DateInput
                  value={startDate}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => setStartDate(e.target.value)}
                  // className="block w-full px-3 py-2 border border-border dark:border-border rounded-md shadow-sm focus:ring-ring focus:border-ring dark:focus:ring-ring dark:focus:border-ring bg-background dark:bg-card text-foreground dark:text-foreground transition-colors duration-200"
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-muted-foreground dark:text-muted-foreground mb-1">Hasta</label>
                <div className="relative">
                  <DateInput
                  value={endDate}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEndDate(e.target.value)}
                  // className="block w-full px-3 py-2 border border-border dark:border-border rounded-md shadow-sm focus:ring-ring focus:border-ring dark:focus:ring-ring dark:focus:border-ring bg-background dark:bg-card text-foreground dark:text-foreground transition-colors duration-200"
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-muted-foreground dark:text-muted-foreground mb-1">Temporalidad</label>
                <select
                  value={temporality}
                  onChange={(e) => setTemporality(e.target.value as TemporalityType)}
                  className="block w-full px-3 py-2 border border-border dark:border-border rounded-md shadow-sm focus:ring-ring focus:border-ring dark:focus:ring-ring dark:focus:border-ring bg-background dark:bg-card text-foreground dark:text-foreground transition-colors duration-200"
                >
                  <option value="weekly">Semanal</option>
                  <option value="monthly">Mensual</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-muted-foreground dark:text-muted-foreground mb-1">Métrica Detallada</label>
                <select
                  value={metricType}
                  onChange={(e) => setMetricType(e.target.value as MetricType)}
                  className="block w-full px-3 py-2 border border-border dark:border-border rounded-md shadow-sm focus:ring-ring focus:border-ring dark:focus:ring-ring dark:focus:border-ring bg-background dark:bg-card text-foreground dark:text-foreground transition-colors duration-200"
                >
                  <option value="food">Food Cost</option>
                  <option value="beverage">Beverage Cost</option>
                  <option value="mix">Mix Cost</option>
                </select>
              </div>
            </div>
            <div className="mt-4 flex justify-end">
              <button
                onClick={handleApplyFilters}
                className="px-4 py-2 bg-primary dark:bg-primary text-primary-foreground dark:text-primary-foreground rounded-md hover:bg-primary/90 dark:hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-ring focus:ring-offset-2 dark:focus:ring-offset-background transition-colors duration-200"
              >
                Aplicar Filtros
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Content */}
      <div className="flex-1 p-6 overflow-auto">
        {loading && (
          <div className="flex items-center justify-center p-8 min-h-[400px] dark:bg-background">
            <div className="text-center">
              <div className="w-12 h-12 border-4 border-t-dashboard-blue dark:border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
              <p className="text-foreground dark:text-foreground">sobreprima.....</p>
            </div>
          </div>
        )}

        {error && (
          <div className="bg-destructive/10 dark:bg-destructive/20 border-l-4 border-destructive dark:border-destructive p-4 mb-6">
            <div className="flex">
              <div className="ml-3">
                <p className="text-sm text-destructive dark:text-destructive-foreground">
                  {error}
                </p>
              </div>
            </div>
          </div>
        )}

        {!loading && !error && (
          <>
            {/* Main indicators section */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              {(['food_cost', 'beverage_cost', 'mix_cost'] as const).map((metric) => {
                // Calculate the metric average
                const avgValue = data.length
                  ? data.reduce((sum, item) => sum + (item[metric] as number), 0) / data.length
                  : 0;

                // Determine the last trend (last value vs second to last)
                const lastValue = data.length >= 1 ? data[data.length - 1][metric] as number : 0;
                const previousValue = data.length >= 2 ? data[data.length - 2][metric] as number : lastValue;
                const trend = lastValue - previousValue;

                return (
                  <div key={metric} className="bg-card dark:bg-card rounded-lg shadow p-6 border border-border dark:border-border transition-colors duration-200">
                    <div className="flex justify-between items-start">
                      <div>
                        <p className="text-sm font-medium text-muted-foreground dark:text-muted-foreground">
                          {metric === 'food_cost' ? 'Food Cost' : metric === 'beverage_cost' ? 'Beverage Cost' : 'Mix Cost'}
                        </p>
                        <h3 className="mt-1 text-3xl font-semibold text-foreground dark:text-foreground">{formatPercentage(avgValue)}</h3>
                      </div>
                      <div
                        className={`flex items-center px-2.5 py-0.5 rounded text-xs font-medium ${trend < 0
                          ? 'text-muted-foreground dark:text-muted-foreground'
                          : trend > 0
                            ? 'bg-dashboard-red/10 dark:bg-dashboard-red/20 text-dashboard-red dark:text-dashboard-red'
                            : 'bg-muted dark:bg-muted text-muted-foreground dark:text-muted-foreground'
                          }`}
                      >
                        {trend !== 0 && (
                          <span className="mr-1">
                            {trend < 0 ? '↓' : '↑'}
                          </span>
                        )}
                        {Math.abs(trend).toFixed(2)}%
                      </div>
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground dark:text-muted-foreground">Promedio del período</p>
                  </div>
                );
              })}
            </div>

            {/* Comparison chart */}
            <div className="bg-card dark:bg-card rounded-lg shadow p-6 border border-border dark:border-border mb-8 transition-colors duration-200">
              <h3 className="text-lg font-medium text-foreground dark:text-foreground mb-4">Comparativa de Costos ({temporality === 'weekly' ? 'Semanal' : 'Mensual'})</h3>
              <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart
                    data={data}
                    margin={{ top: 5, right: 30, left: 20, bottom: 30 }}
                  >
                    <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                    <XAxis
                      dataKey="period"
                      angle={-45}
                      textAnchor="end"
                      height={70}
                      interval={0}
                      tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
                    />
                    <YAxis
                      tickFormatter={formatPercentage}
                      domain={[0, 'auto']}
                      label={{ value: 'Porcentaje (%)', angle: -90, position: 'insideLeft' }}
                      tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
                    />
                    <Tooltip
                      formatter={(value) => [formatPercentage(value as number), 'Porcentaje']}
                      contentStyle={{
                        backgroundColor: 'hsl(var(--popover))',
                        border: '1px solid hsl(var(--border))',
                        borderRadius: '8px',
                        color: 'hsl(var(--popover-foreground))'
                      }}
                    />
                    <Legend />
                    <Line
                      type="monotone"
                      dataKey="food_cost"
                      name="Food Cost"
                      stroke={getChartColor('food_cost')}
                      activeDot={{ r: 8 }}
                    />
                    <Line
                      type="monotone"
                      dataKey="beverage_cost"
                      name="Beverage Cost"
                      stroke={getChartColor('beverage_cost')}
                      activeDot={{ r: 8 }}
                    />
                    <Line
                      type="monotone"
                      dataKey="mix_cost"
                      name="Mix Cost"
                      stroke={getChartColor('mix_cost')}
                      activeDot={{ r: 8 }}
                    />
                  </LineChart>
                </ResponsiveContainer>
              </div>
            </div>

            {/* Detailed chart */}
            <div className="bg-card dark:bg-card rounded-lg shadow p-6 border border-border dark:border-border transition-colors duration-200">
              <h3 className="text-lg font-medium text-foreground dark:text-foreground mb-4">
                Detalle Diario: {metricType === 'food' ? 'Food Cost' : metricType === 'beverage' ? 'Beverage Cost' : 'Mix Cost'}
              </h3>
              <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart
                    data={detailedData}
                    margin={{ top: 5, right: 30, left: 20, bottom: 30 }}
                    className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
                  >
                    <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                    <XAxis
                      dataKey="date"
                      angle={-45}
                      textAnchor="end"
                      height={70}
                      tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
                    />
                    <YAxis
                      tickFormatter={formatPercentage}
                      domain={[0, 'auto']}
                      label={{ value: 'Porcentaje (%)', angle: -90, position: 'insideLeft' }}
                      tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
                    />
                    <Tooltip
                      formatter={(value, name) => [formatPercentage(value as number), name === 'value' ? 'Porcentaje' : name]}
                      labelFormatter={(label) => {
                        const item = detailedData.find(d => d.date === label);
                        return `${label} (${item?.day_name || ''})`;
                      }}
                      contentStyle={{
                        backgroundColor: 'hsl(var(--popover))',
                        border: '1px solid hsl(var(--border))',
                        borderRadius: '8px',
                        color: 'hsl(var(--popover-foreground))'
                      }}
                    />
                    <Legend />
                    <Bar
                      dataKey="value"
                      name={metricType === 'food' ? 'Food Cost' : metricType === 'beverage' ? 'Beverage Cost' : 'Mix Cost'}
                      fill={getChartColor(metricType === 'food' ? 'food_cost' : metricType === 'beverage' ? 'beverage_cost' : 'mix_cost')}
                    />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default CostMetricsPanel;