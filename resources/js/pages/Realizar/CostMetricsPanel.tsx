import React, { useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, BarChart, Bar } from 'recharts';
import { Calendar, ChevronDown, Filter, RefreshCw } from 'lucide-react';

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
    <div className="flex flex-col h-full bg-white rounded-lg bg-white shadow-lg p-4 md:p-6 animate-fade-in shadow mt-[50px]">
      {/* Encabezado */}
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex justify-between items-center">
          <h2 className="text-2xl font-semibold text-gray-800">Métricas de Costos</h2>
          <div className="flex items-center space-x-2">
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              <Filter className="w-4 h-4 mr-2" />
              Filtros
              <ChevronDown className={`w-4 h-4 ml-1 transform ${showFilters ? 'rotate-180' : ''}`} />
            </button>
            <button
              onClick={() => {
                fetchCostMetrics();
                fetchDetailedMetrics();
              }}
              className="flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              <RefreshCw className="w-4 h-4 mr-1" />
              Actualizar
            </button>
          </div>
        </div>
        
        {/* Filtros */}
        {showFilters && (
          <div className="mt-4 p-4 bg-gray-50 rounded-md border border-gray-200">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <div className="relative">
                  <input
                    type="date"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                    className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                  <Calendar className="absolute right-3 top-2.5 h-4 w-4 text-gray-400" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <div className="relative">
                  <input
                    type="date"
                    value={endDate}
                    onChange={(e) => setEndDate(e.target.value)}
                    className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                  <Calendar className="absolute right-3 top-2.5 h-4 w-4 text-gray-400" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Temporalidad</label>
                <select
                  value={temporality}
                  onChange={(e) => setTemporality(e.target.value as TemporalityType)}
                  className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="weekly">Semanal</option>
                  <option value="monthly">Mensual</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Métrica Detallada</label>
                <select
                  value={metricType}
                  onChange={(e) => setMetricType(e.target.value as MetricType)}
                  className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
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
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                Aplicar Filtros
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Contenido */}
      <div className="flex-1 p-6 overflow-auto">
        {loading && (
          <div className="flex justify-center items-center h-64">
            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
          </div>
        )}

        {error && (
          <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div className="flex">
              <div className="ml-3">
                <p className="text-sm text-red-700">
                  {error}
                </p>
              </div>
            </div>
          </div>
        )}

        {!loading && !error && (
          <>
            {/* Sección de indicadores principales */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              {(['food_cost', 'beverage_cost', 'mix_cost'] as const).map((metric) => {
                // Calcular el promedio de la métrica
                const avgValue = data.length 
                  ? data.reduce((sum, item) => sum + (item[metric] as number), 0) / data.length 
                  : 0;
                
                // Determinar la última tendencia (último valor vs penúltimo)
                const lastValue = data.length >= 1 ? data[data.length - 1][metric] as number : 0;
                const previousValue = data.length >= 2 ? data[data.length - 2][metric] as number : lastValue;
                const trend = lastValue - previousValue;

                return (
                  <div key={metric} className="bg-white rounded-lg shadow p-6 border border-gray-100">
                    <div className="flex justify-between items-start">
                      <div>
                        <p className="text-sm font-medium text-gray-500">
                          {metric === 'food_cost' ? 'Food Cost' : metric === 'beverage_cost' ? 'Beverage Cost' : 'Mix Cost'}
                        </p>
                        <h3 className="mt-1 text-3xl font-semibold text-gray-900">{formatPercentage(avgValue)}</h3>
                      </div>
                      <div 
                        className={`flex items-center px-2.5 py-0.5 rounded text-xs font-medium ${
                          trend < 0 ? 'bg-green-100 text-green-800' : trend > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'
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
                    <p className="mt-1 text-sm text-gray-500">Promedio del período</p>
                  </div>
                );
              })}
            </div>

            {/* Gráfico de comparación */}
            <div className="bg-white rounded-lg shadow p-6 border border-gray-100 mb-8">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Comparativa de Costos ({temporality === 'weekly' ? 'Semanal' : 'Mensual'})</h3>
              <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart
                    data={data}
                    margin={{ top: 5, right: 30, left: 20, bottom: 30 }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis 
                      dataKey="period" 
                      angle={-45} 
                      textAnchor="end" 
                      height={70} 
                      interval={0}
                      tick={{ fontSize: 12 }}
                    />
                    <YAxis 
                      tickFormatter={formatPercentage} 
                      domain={[0, 'auto']} 
                      label={{ value: 'Porcentaje (%)', angle: -90, position: 'insideLeft' }}
                    />
                    <Tooltip formatter={(value) => [formatPercentage(value as number), 'Porcentaje']} />
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

            {/* Gráfico detallado */}
            <div className="bg-white rounded-lg shadow p-6 border border-gray-100">
              <h3 className="text-lg font-medium text-gray-900 mb-4">
                Detalle Diario: {metricType === 'food' ? 'Food Cost' : metricType === 'beverage' ? 'Beverage Cost' : 'Mix Cost'}
              </h3>
              <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart
                    data={detailedData}
                    margin={{ top: 5, right: 30, left: 20, bottom: 30 }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis 
                      dataKey="date" 
                      angle={-45} 
                      textAnchor="end" 
                      height={70}
                      tick={{ fontSize: 12 }}
                    />
                    <YAxis 
                      tickFormatter={formatPercentage} 
                      domain={[0, 'auto']} 
                      label={{ value: 'Porcentaje (%)', angle: -90, position: 'insideLeft' }}
                    />
                    <Tooltip 
                      formatter={(value, name) => [formatPercentage(value as number), name === 'value' ? 'Porcentaje' : name]}
                      labelFormatter={(label) => {
                        const item = detailedData.find(d => d.date === label);
                        return `${label} (${item?.day_name || ''})`;
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