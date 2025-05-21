import React, { useState, useEffect } from 'react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

// Define TypeScript interfaces for the component's state
interface TemporalParams {
  fecha_inicio: string;
  fecha_fin: string;
  agrupacion: 'dia' | 'semana' | 'mes' | 'anio';
}

interface ComparativoParams {
  periodo1_inicio: string;
  periodo1_fin: string;
  periodo2_inicio: string;
  periodo2_fin: string;
  agrupacion: 'dia' | 'semana' | 'mes';
}

interface Dataset {
  label: string;
  data: number[];
  borderColor: string;
  backgroundColor: string;
}

interface ChartData {
  labels: string[];
  datasets: Dataset[];
}

interface PeriodData {
  fecha_inicio: string;
  fecha_fin: string;
  total: number;
  promedio: number;
  minimo: number;
  maximo: number;
}

interface ComparacionData {
  variacion_total: number;
  variacion_promedio: number;
}

interface ComparativoData {
  status: string;
  periodo1: PeriodData;
  periodo2: PeriodData;
  comparacion: ComparacionData;
  grafico: ChartData;
}

interface FormattedDataPoint {
  fecha: string;
  [key: string]: string | number;
}

interface ComparativeChartDataPoint {
  metrica: string;
  [key: string]: string | number;
}

type TabType = 'temporal' | 'comparativo';

const Pinta: React.FC = () => {
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [chartData, setChartData] = useState<ChartData>({ labels: [], datasets: [] });
  const [lastUpdate, setLastUpdate] = useState<Date | null>(null);

  // Estado para la pestaña activa: temporal o comparativo
  const [activeTab, setActiveTab] = useState<TabType>('temporal');

  // Configuración del intervalo de tiempo y agrupación - Gráfico Temporal
  const defaultTemporalParams: TemporalParams = {
    fecha_inicio: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0], // 30 días atrás
    fecha_fin: new Date().toISOString().split('T')[0], // hoy
    agrupacion: 'dia'
  };

  // Configuración para el gráfico comparativo
  const defaultComparativoParams: ComparativoParams = {
    periodo1_inicio: new Date(new Date().setMonth(new Date().getMonth() - 2)).toISOString().split('T')[0], // 2 meses atrás
    periodo1_fin: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0], // 1 mes atrás
    periodo2_inicio: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0], // 1 mes atrás
    periodo2_fin: new Date().toISOString().split('T')[0], // hoy
    agrupacion: 'dia'
  };

  const [temporalParams, setTemporalParams] = useState<TemporalParams>(defaultTemporalParams);
  const [comparativoParams, setComparativoParams] = useState<ComparativoParams>(defaultComparativoParams);
  const [comparativoData, setComparativoData] = useState<ComparativoData | null>(null);

  // Función para cargar los datos del índice pinta temporal
  const fetchTemporalData = async (): Promise<void> => {
    try {
      setLoading(true);
      
      // Construir URL con parámetros
      const queryParams = new URLSearchParams({
        fecha_inicio: temporalParams.fecha_inicio,
        fecha_fin: temporalParams.fecha_fin,
        agrupacion: temporalParams.agrupacion
      });
      
      const response = await fetch(`/api/indice-pinta/temporal?${queryParams.toString()}`);
      
      if (!response.ok) {
        throw new Error('Error al cargar los datos temporales del índice pinta');
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setChartData(data.grafico);
        setLastUpdate(new Date());
      } else {
        throw new Error(data.message || 'Error desconocido');
      }
      
      setError(null);
    } catch (err) {
      console.error('Error fetching temporal data:', err);
      setError(err instanceof Error ? err.message : 'Error desconocido');
    } finally {
      setLoading(false);
    }
  };

  // Función para cargar los datos comparativos
  const fetchComparativoData = async (): Promise<void> => {
    try {
      setLoading(true);
      
      // Construir URL con parámetros
      const queryParams = new URLSearchParams({
        periodo1_inicio: comparativoParams.periodo1_inicio,
        periodo1_fin: comparativoParams.periodo1_fin,
        periodo2_inicio: comparativoParams.periodo2_inicio,
        periodo2_fin: comparativoParams.periodo2_fin,
        agrupacion: comparativoParams.agrupacion
      });
      
      const response = await fetch(`/api/indice-pinta/comparativo?${queryParams.toString()}`);
      
      if (!response.ok) {
        throw new Error('Error al cargar los datos comparativos del índice pinta');
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setComparativoData(data);
        setLastUpdate(new Date());
      } else {
        throw new Error(data.message || 'Error desconocido');
      }
      
      setError(null);
    } catch (err) {
      console.error('Error fetching comparative data:', err);
      setError(err instanceof Error ? err.message : 'Error desconocido');
    } finally {
      setLoading(false);
    }
  };

  // Función para actualizar los parámetros temporales
  const handleTemporalParamChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>): void => {
    const { name, value } = e.target;
    setTemporalParams(prev => ({
      ...prev,
      [name]: value
    }) as TemporalParams);
  };

  // Función para actualizar los parámetros comparativos
  const handleComparativoParamChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>): void => {
    const { name, value } = e.target;
    setComparativoParams(prev => ({
      ...prev,
      [name]: value
    }) as ComparativoParams);
  };

  // Cargar datos iniciales y establecer intervalo
  useEffect(() => {
    if (activeTab === 'temporal') {
      fetchTemporalData();
      
      // Establecer intervalo para actualizar cada 10 segundos solo para la vista temporal
      const intervalId = setInterval(() => {
        fetchTemporalData();
      }, 10000); // 10 segundos
      
      // Limpiar intervalo cuando el componente se desmonte o cambie la pestaña
      return () => clearInterval(intervalId);
    } else if (activeTab === 'comparativo') {
      fetchComparativoData();
    }
  }, [activeTab, temporalParams, comparativoParams]);

  // Transformar datos para Recharts - Gráfico Temporal
  const chartFormattedData: FormattedDataPoint[] = chartData.labels?.map((label, index) => {
    const dataPoint: FormattedDataPoint = { fecha: label };
    
    chartData.datasets?.forEach(dataset => {
      dataPoint[dataset.label] = dataset.data[index];
    });
    
    return dataPoint;
  }) || [];

  // Transformar datos para Recharts - Gráfico Comparativo
  const comparativeChartData: ComparativeChartDataPoint[] = comparativoData?.grafico.labels.map((label, index) => {
    const dataPoint: ComparativeChartDataPoint = {
      metrica: label,
      [comparativoData.grafico.datasets[0].label]: comparativoData.grafico.datasets[0].data[index],
      [comparativoData.grafico.datasets[1].label]: comparativoData.grafico.datasets[1].data[index]
    };
    return dataPoint;
  }) || [];

  return (
    <div className="bg-white p-6 rounded-lg shadow-md">
      <h1 className="text-3xl font-bold mb-6">Dashboard Índice Pinta</h1>
      
      {/* Pestañas */}
      <div className="flex border-b border-gray-200 mb-6">
        <button
          className={`mr-4 py-2 px-4 font-medium ${activeTab === 'temporal' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'}`}
          onClick={() => setActiveTab('temporal')}
        >
          Evolución Temporal
        </button>
        <button
          className={`mr-4 py-2 px-4 font-medium ${activeTab === 'comparativo' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'}`}
          onClick={() => setActiveTab('comparativo')}
        >
          Comparativo
        </button>
      </div>
      
      <p className="text-gray-600 mb-4">
        {lastUpdate ? `Última actualización: ${lastUpdate.toLocaleTimeString()}` : 'Cargando datos...'}
        {activeTab === 'temporal' && ' (actualización automática cada 10 segundos)'}
      </p>
      
      {/* Filtros según pestaña activa */}
      {activeTab === 'temporal' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-gray-50 p-4 rounded-md">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input
              type="date"
              name="fecha_inicio"
              value={temporalParams.fecha_inicio}
              onChange={handleTemporalParamChange}
              className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input
              type="date"
              name="fecha_fin"
              value={temporalParams.fecha_fin}
              onChange={handleTemporalParamChange}
              className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Agrupación</label>
            <select
              name="agrupacion"
              value={temporalParams.agrupacion}
              onChange={handleTemporalParamChange}
              className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
            >
              <option value="dia">Diaria</option>
              <option value="semana">Semanal</option>
              <option value="mes">Mensual</option>
              <option value="anio">Anual</option>
            </select>
          </div>
        </div>
      )}
      
      {activeTab === 'comparativo' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-md">
          <div className="border-r border-gray-200 pr-4">
            <h3 className="font-semibold mb-2 text-blue-600">Período 1</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                <input
                  type="date"
                  name="periodo1_inicio"
                  value={comparativoParams.periodo1_inicio}
                  onChange={handleComparativoParamChange}
                  className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                <input
                  type="date"
                  name="periodo1_fin"
                  value={comparativoParams.periodo1_fin}
                  onChange={handleComparativoParamChange}
                  className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
                />
              </div>
            </div>
          </div>
          <div className="pl-4">
            <h3 className="font-semibold mb-2 text-green-600">Período 2</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                <input
                  type="date"
                  name="periodo2_inicio"
                  value={comparativoParams.periodo2_inicio}
                  onChange={handleComparativoParamChange}
                  className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                <input
                  type="date"
                  name="periodo2_fin"
                  value={comparativoParams.periodo2_fin}
                  onChange={handleComparativoParamChange}
                  className="w-full rounded-md border-gray-300 shadow-sm p-2 border"
                />
              </div>
            </div>
          </div>
          <div className="col-span-1 md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-1">Agrupación</label>
            <select
              name="agrupacion"
              value={comparativoParams.agrupacion}
              onChange={handleComparativoParamChange}
              className="w-full md:w-1/3 rounded-md border-gray-300 shadow-sm p-2 border"
            >
              <option value="dia">Diaria</option>
              <option value="semana">Semanal</option>
              <option value="mes">Mensual</option>
            </select>
          </div>
        </div>
      )}
      
      {/* Mensajes de carga y error */}
      {loading && (
        <div className="flex justify-center items-center h-12 mb-4">
          <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
          <p className="ml-2 text-blue-500">Cargando datos...</p>
        </div>
      )}
      
      {error && (
        <div className="bg-red-100 p-4 rounded-md mb-4">
          <p className="text-red-700">Error: {error}</p>
        </div>
      )}
      
      {/* Visualización según pestaña activa */}
      {activeTab === 'temporal' && !loading && chartFormattedData.length > 0 && (
        <div className="h-96 mb-6">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart
              data={chartFormattedData}
              margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="fecha" />
              <YAxis />
              <Tooltip />
              <Legend />
              {chartData.datasets?.map((dataset, index) => (
                <Line
                  key={index}
                  type="monotone"
                  dataKey={dataset.label}
                  stroke={dataset.borderColor}
                  fill={dataset.backgroundColor}
                  activeDot={{ r: 8 }}
                />
              ))}
            </LineChart>
          </ResponsiveContainer>
        </div>
      )}
      
      {activeTab === 'comparativo' && !loading && comparativoData && (
        <div>
          {/* Sección de métricas comparativas */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div className="bg-blue-50 p-4 rounded-lg">
              <h3 className="text-lg font-semibold mb-2 text-blue-600">Período 1</h3>
              <p className="text-sm text-gray-500 mb-2">
                {comparativoData.periodo1.fecha_inicio} al {comparativoData.periodo1.fecha_fin}
              </p>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-600">Total:</p>
                  <p className="text-xl font-bold">{comparativoData.periodo1.total.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Promedio:</p>
                  <p className="text-xl font-bold">{comparativoData.periodo1.promedio.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Mínimo:</p>
                  <p className="text-lg">{comparativoData.periodo1.minimo.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Máximo:</p>
                  <p className="text-lg">{comparativoData.periodo1.maximo.toFixed(2)}</p>
                </div>
              </div>
            </div>
            
            <div className="bg-green-50 p-4 rounded-lg">
              <h3 className="text-lg font-semibold mb-2 text-green-600">Período 2</h3>
              <p className="text-sm text-gray-500 mb-2">
                {comparativoData.periodo2.fecha_inicio} al {comparativoData.periodo2.fecha_fin}
              </p>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-600">Total:</p>
                  <p className="text-xl font-bold">{comparativoData.periodo2.total.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Promedio:</p>
                  <p className="text-xl font-bold">{comparativoData.periodo2.promedio.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Mínimo:</p>
                  <p className="text-lg">{comparativoData.periodo2.minimo.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Máximo:</p>
                  <p className="text-lg">{comparativoData.periodo2.maximo.toFixed(2)}</p>
                </div>
              </div>
            </div>
          </div>
          
          {/* Sección variación */}
          <div className="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 className="text-lg font-semibold mb-4">Variación entre períodos</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="flex items-center">
                <div className={`text-3xl font-bold ${comparativoData.comparacion.variacion_total >= 0 ? 'text-green-600' : 'text-red-600'} mr-2`}>
                  {comparativoData.comparacion.variacion_total >= 0 ? '+' : ''}{comparativoData.comparacion.variacion_total}%
                </div>
                <div className="text-gray-600">Variación total</div>
              </div>
              <div className="flex items-center">
                <div className={`text-3xl font-bold ${comparativoData.comparacion.variacion_promedio >= 0 ? 'text-green-600' : 'text-red-600'} mr-2`}>
                  {comparativoData.comparacion.variacion_promedio >= 0 ? '+' : ''}{comparativoData.comparacion.variacion_promedio}%
                </div>
                <div className="text-gray-600">Variación promedio</div>
              </div>
            </div>
          </div>
          
          {/* Gráfico comparativo */}
          <div className="h-96">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={comparativeChartData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
              >
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="metrica" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar 
                  dataKey={comparativoData.grafico.datasets[0].label} 
                  fill="rgba(59, 130, 246, 0.7)" 
                />
                <Bar 
                  dataKey={comparativoData.grafico.datasets[1].label} 
                  fill="rgba(16, 185, 129, 0.7)" 
                />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}
      
      {/* Mensaje si no hay datos */}
      {!loading && 
       ((activeTab === 'temporal' && chartFormattedData.length === 0) || 
        (activeTab === 'comparativo' && !comparativoData)) && 
       !error && (
        <div className="flex justify-center items-center h-64 bg-gray-50 rounded-lg">
          <p className="text-lg text-gray-500">No hay datos disponibles para los parámetros seleccionados</p>
        </div>
      )}
    </div>
  );
};

export default Pinta;