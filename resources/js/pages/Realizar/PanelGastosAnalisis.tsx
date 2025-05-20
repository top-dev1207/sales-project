import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { AlertCircle, Calendar, RefreshCw, DollarSign, BarChart2, PieChart as PieChartIcon } from 'lucide-react';

// Interfaces para los datos
interface Periodo {
  fecha_inicio: string;
  fecha_fin: string;
}

interface RubroGasto {
  rubro_id: number;
  rubro_nombre: string;
  importe: number;
  porcentaje_ventas?: number;
  porcentaje_gastos_totales?: number;
}

interface DashboardData {
  gastos_sobre_ventas: RubroGasto[];
  gastos_sobre_total: RubroGasto[];
  gastos_relevantes: RubroGasto[];
}

interface ApiResponse {
  status: string;
  periodo: Periodo;
  ventas_totales?: number;
  gastos_totales?: number;
  data: RubroGasto[] | DashboardData;
  message?: string;
}

// Colores para los gráficos
const COLORS = [
  '#8884d8', '#83a6ed', '#8dd1e1', '#82ca9d', '#a4de6c',
  '#d0ed57', '#ffc658', '#ff8042', '#ff6361', '#bc5090',
  '#58508d', '#003f5c', '#7a5195', '#ef5675', '#ffa600'
];

const formatoMoneda = (valor: number): string => {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    minimumFractionDigits: 2
  }).format(valor);
};

const formatoFecha = (fecha: string): string => {
  const f = new Date(fecha);
  return f.toLocaleDateString('es-AR');
};

const PanelGastosAnalisis: React.FC = () => {
  const [fechaInicio, setFechaInicio] = useState<string>('');
  const [fechaFin, setFechaFin] = useState<string>('');
  const [dashboard, setDashboard] = useState<ApiResponse | null>(null);
  const [cargando, setCargando] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [vistaActiva, setVistaActiva] = useState<'sobre-ventas' | 'sobre-total' | 'mas-relevantes' | 'dashboard'>('dashboard');

  useEffect(() => {
    // Establecer fechas predeterminadas (mes actual)
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    setFechaInicio(inicioMes.toISOString().split('T')[0]);
    setFechaFin(hoy.toISOString().split('T')[0]);

    // Cargar datos iniciales
    cargarDatos('dashboard');
  }, []);

  const cargarDatos = async (endpoint: string) => {
    if (!fechaInicio || !fechaFin) return;
    
    setCargando(true);
    setError(null);
    
    try {
      // Construir la URL con los parámetros de consulta
      const url = new URL(`/api/gastos-analisis/${endpoint}`, window.location.origin);
      url.searchParams.append('fecha_inicio', fechaInicio);
      url.searchParams.append('fecha_fin', fechaFin);
      
      const response = await fetch(url.toString());
      
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }
      
      const data = await response.json();
      
      setDashboard(data);
      setVistaActiva(endpoint as any);
    } catch (err) {
      console.error('Error al cargar datos:', err);
      setError('Error al cargar los datos. Por favor intente nuevamente.');
    } finally {
      setCargando(false);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    cargarDatos(vistaActiva);
  };

  const renderGastosSobreVentas = () => {
    if (!dashboard || !dashboard.data || dashboard.status !== 'success' || !('porcentaje_ventas' in (dashboard.data as RubroGasto[])[0])) {
      return (
        <div className="text-center p-4">
          <p>No hay datos disponibles para mostrar.</p>
        </div>
      );
    }

    const data = dashboard.data as RubroGasto[];
    data.sort((a, b) => (b.porcentaje_ventas || 0) - (a.porcentaje_ventas || 0));

    return (
      <div className="mt-4">
        <h3 className="text-lg font-medium mb-2">Gastos como porcentaje de ventas totales</h3>
        <div className="mb-2">
          <p className="text-sm">Ventas totales: {formatoMoneda(dashboard.ventas_totales || 0)}</p>
        </div>
        <div className="h-96">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              data={data}
              layout="vertical"
              margin={{ top: 20, right: 30, left: 150, bottom: 5 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis type="number" domain={[0, Math.max(...data.map(d => d.porcentaje_ventas || 0)) * 1.1]} unit="%" />
              <YAxis dataKey="rubro_nombre" type="category" width={140} />
              <Tooltip 
                formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                labelFormatter={(label) => `Rubro: ${label}`}
              />
              <Legend />
              <Bar dataKey="porcentaje_ventas" name="% de Ventas" fill="#8884d8" />
            </BarChart>
          </ResponsiveContainer>
        </div>
        <div className="mt-4 overflow-x-auto">
          <table className="min-w-full bg-white border rounded-lg">
            <thead className="bg-gray-100">
              <tr>
                <th className="py-2 px-4 border-b text-left">Rubro</th>
                <th className="py-2 px-4 border-b text-right">Importe</th>
                <th className="py-2 px-4 border-b text-right">% de Ventas</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => (
                <tr key={index} className={index % 2 === 0 ? 'bg-gray-50' : ''}>
                  <td className="py-2 px-4 border-b">{item.rubro_nombre}</td>
                  <td className="py-2 px-4 border-b text-right">{formatoMoneda(item.importe)}</td>
                  <td className="py-2 px-4 border-b text-right">{item.porcentaje_ventas?.toFixed(2)}%</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    );
  };

  const renderGastosSobreTotal = () => {
    if (!dashboard || !dashboard.data || dashboard.status !== 'success' || !('porcentaje_gastos_totales' in (dashboard.data as RubroGasto[])[0])) {
      return (
        <div className="text-center p-4">
          <p>No hay datos disponibles para mostrar.</p>
        </div>
      );
    }

    const data = dashboard.data as RubroGasto[];
    data.sort((a, b) => (b.porcentaje_gastos_totales || 0) - (a.porcentaje_gastos_totales || 0));
    
    // Filtramos solo los primeros 10 para el gráfico circular
    const pieData = data.slice(0, 10);
    // Agrupamos el resto como "Otros" si hay más de 10 rubros
    if (data.length > 10) {
      const otros = {
        rubro_id: 0,
        rubro_nombre: 'Otros',
        importe: data.slice(10).reduce((sum, item) => sum + item.importe, 0),
        porcentaje_gastos_totales: data.slice(10).reduce((sum, item) => sum + (item.porcentaje_gastos_totales || 0), 0)
      };
      pieData.push(otros);
    }

    return (
      <div className="mt-4">
        <h3 className="text-lg font-medium mb-2">Distribución de gastos por rubro</h3>
        <div className="mb-2">
          <p className="text-sm">Gastos totales: {formatoMoneda(dashboard.gastos_totales || 0)}</p>
        </div>
        <div className="flex flex-col md:flex-row">
          <div className="w-full md:w-1/2 h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={pieData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="porcentaje_gastos_totales"
                  nameKey="rubro_nombre"
                  label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(1)}%`}
                >
                  {pieData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip 
                  formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                  labelFormatter={(name) => `Rubro: ${name}`}
                />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="w-full md:w-1/2 h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={data.slice(0, 8)} // Mostramos solo los 8 más relevantes
                layout="vertical"
                margin={{ top: 20, right: 30, left: 150, bottom: 5 }}
              >
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis type="number" unit="%" />
                <YAxis dataKey="rubro_nombre" type="category" width={140} />
                <Tooltip 
                  formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                  labelFormatter={(label) => `Rubro: ${label}`}
                />
                <Legend />
                <Bar dataKey="porcentaje_gastos_totales" name="% del Total" fill="#82ca9d" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className="mt-4 overflow-x-auto">
          <table className="min-w-full bg-white border rounded-lg">
            <thead className="bg-gray-100">
              <tr>
                <th className="py-2 px-4 border-b text-left">Rubro</th>
                <th className="py-2 px-4 border-b text-right">Importe</th>
                <th className="py-2 px-4 border-b text-right">% del Total</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => (
                <tr key={index} className={index % 2 === 0 ? 'bg-gray-50' : ''}>
                  <td className="py-2 px-4 border-b">{item.rubro_nombre}</td>
                  <td className="py-2 px-4 border-b text-right">{formatoMoneda(item.importe)}</td>
                  <td className="py-2 px-4 border-b text-right">{item.porcentaje_gastos_totales?.toFixed(2)}%</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    );
  };

  const renderGastosMasRelevantes = () => {
    if (!dashboard || !dashboard.data || dashboard.status !== 'success' || !('porcentaje_gastos_totales' in (dashboard.data as RubroGasto[])[0])) {
      return (
        <div className="text-center p-4">
          <p>No hay datos disponibles para mostrar.</p>
        </div>
      );
    }

    const data = dashboard.data as RubroGasto[];

    return (
      <div className="mt-4">
        <h3 className="text-lg font-medium mb-2">Rubros de gastos más relevantes</h3>
        <div className="mb-2">
          <p className="text-sm">Gastos totales: {formatoMoneda(dashboard.gastos_totales || 0)}</p>
        </div>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              data={data}
              margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="rubro_nombre" />
              <YAxis />
              <Tooltip 
                formatter={(value: any, name: string) => [
                  name === "importe" ? formatoMoneda(value) : `${value.toFixed(2)}%`,
                  name === "importe" ? "Importe" : "% del Total"
                ]}
              />
              <Legend />
              <Bar dataKey="importe" name="Importe" fill="#8884d8" />
              <Bar dataKey="porcentaje_gastos_totales" name="% del Total" fill="#82ca9d" />
            </BarChart>
          </ResponsiveContainer>
        </div>
        <div className="mt-4 overflow-x-auto">
          <table className="min-w-full bg-white border rounded-lg">
            <thead className="bg-gray-100">
              <tr>
                <th className="py-2 px-4 border-b text-center">#</th>
                <th className="py-2 px-4 border-b text-left">Rubro</th>
                <th className="py-2 px-4 border-b text-right">Importe</th>
                <th className="py-2 px-4 border-b text-right">% del Total</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => (
                <tr key={index} className={index % 2 === 0 ? 'bg-gray-50' : ''}>
                  <td className="py-2 px-4 border-b text-center">{index + 1}</td>
                  <td className="py-2 px-4 border-b">{item.rubro_nombre}</td>
                  <td className="py-2 px-4 border-b text-right">{formatoMoneda(item.importe)}</td>
                  <td className="py-2 px-4 border-b text-right">{item.porcentaje_gastos_totales?.toFixed(2)}%</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    );
  };

  const renderDashboard = () => {
    if (!dashboard || !dashboard.data || dashboard.status !== 'success' || !('gastos_sobre_ventas' in dashboard.data)) {
      return (
        <div className="text-center p-4">
          <p>No hay datos disponibles para mostrar en el dashboard.</p>
        </div>
      );
    }

    const data = dashboard.data as DashboardData;

    // Preparar datos para los gráficos de resumen
    const topGastosSobreVentas = [...data.gastos_sobre_ventas]
      .sort((a, b) => (b.porcentaje_ventas || 0) - (a.porcentaje_ventas || 0))
      .slice(0, 5);

    const gastosMasRelevantes = data.gastos_relevantes;

    return (
      <div className="mt-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div className="bg-white p-4 rounded-lg shadow">
            <h3 className="text-lg font-medium mb-2">Resumen General</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-blue-50 p-3 rounded-lg">
                <p className="text-sm text-blue-500">Ventas Totales</p>
                <p className="text-xl font-bold">{formatoMoneda(dashboard.ventas_totales || 0)}</p>
              </div>
              <div className="bg-red-50 p-3 rounded-lg">
                <p className="text-sm text-red-500">Gastos Totales</p>
                <p className="text-xl font-bold">{formatoMoneda(dashboard.gastos_totales || 0)}</p>
              </div>
              <div className="bg-green-50 p-3 rounded-lg">
                <p className="text-sm text-green-500">Rentabilidad</p>
                <p className="text-xl font-bold">
                  {formatoMoneda((dashboard.ventas_totales || 0) - (dashboard.gastos_totales || 0))}
                </p>
              </div>
              <div className="bg-purple-50 p-3 rounded-lg">
                <p className="text-sm text-purple-500">Margen</p>
                <p className="text-xl font-bold">
                  {(((dashboard.ventas_totales || 0) - (dashboard.gastos_totales || 0)) / (dashboard.ventas_totales || 1) * 100).toFixed(2)}%
                </p>
              </div>
            </div>
          </div>
          
          <div className="bg-white p-4 rounded-lg shadow">
            <h3 className="text-lg font-medium mb-2">Período de Análisis</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="text-sm text-gray-500">Fecha Inicio</p>
                <p className="text-lg font-semibold">{formatoFecha(dashboard.periodo.fecha_inicio)}</p>
              </div>
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="text-sm text-gray-500">Fecha Fin</p>
                <p className="text-lg font-semibold">{formatoFecha(dashboard.periodo.fecha_fin)}</p>
              </div>
            </div>
            <div className="mt-4">
              <p className="text-sm text-gray-500">
                El análisis muestra los gastos clasificados por rubro, permitiendo identificar 
                patrones y áreas de oportunidad para la optimización de recursos.
              </p>
            </div>
          </div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="bg-white p-4 rounded-lg shadow">
            <h3 className="text-lg font-medium mb-2">Top 5 Gastos sobre Ventas</h3>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart
                  data={topGastosSobreVentas}
                  layout="vertical"
                  margin={{ top: 5, right: 30, left: 100, bottom: 5 }}
                >
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis type="number" unit="%" />
                  <YAxis dataKey="rubro_nombre" type="category" width={90} />
                  <Tooltip 
                    formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                  />
                  <Bar dataKey="porcentaje_ventas" name="% de Ventas" fill="#8884d8" />
                </BarChart>
              </ResponsiveContainer>
            </div>
            <div className="text-right mt-2">
              <button 
                onClick={() => cargarDatos('sobre-ventas')}
                className="text-blue-500 hover:text-blue-700 text-sm"
              >
                Ver análisis completo →
              </button>
            </div>
          </div>
          
          <div className="bg-white p-4 rounded-lg shadow">
            <h3 className="text-lg font-medium mb-2">Gastos Más Relevantes</h3>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={gastosMasRelevantes}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="porcentaje_gastos_totales"
                    nameKey="rubro_nombre"
                    label={({ name, percent }) => `${name.substring(0, 12)}${name.length > 12 ? '...' : ''}: ${(percent * 100).toFixed(1)}%`}
                  >
                    {gastosMasRelevantes.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip 
                    formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                    labelFormatter={(name) => `Rubro: ${name}`}
                  />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="text-right mt-2">
              <button 
                onClick={() => cargarDatos('mas-relevantes')}
                className="text-blue-500 hover:text-blue-700 text-sm"
              >
                Ver análisis completo →
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  };

  const renderContent = () => {
    if (cargando) {
      return (
        <div className="text-center p-10">
          <RefreshCw className="animate-spin h-10 w-10 text-blue-500 mx-auto mb-4" />
          <p>Cargando datos...</p>
        </div>
      );
    }

    if (error) {
      return (
        <div className="text-center p-10 text-red-500">
          <AlertCircle className="h-10 w-10 mx-auto mb-4" />
          <p>{error}</p>
        </div>
      );
    }

    if (!dashboard) {
      return (
        <div className="text-center p-10">
          <p>Seleccione un período y haga clic en "Analizar" para ver los resultados.</p>
        </div>
      );
    }

    if (dashboard.status === 'warning') {
      return (
        <div className="text-center p-10 text-yellow-500">
          <AlertCircle className="h-10 w-10 mx-auto mb-4" />
          <p>{dashboard.message || 'No hay datos disponibles para el período seleccionado.'}</p>
        </div>
      );
    }

    switch(vistaActiva) {
      case 'sobre-ventas':
        return renderGastosSobreVentas();
      case 'sobre-total':
        return renderGastosSobreTotal();
      case 'mas-relevantes':
        return renderGastosMasRelevantes();
      case 'dashboard':
      default:
        return renderDashboard();
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-lg p-4 md:p-6 animate-fade-in shadow mt-[50px]">
      <div className="mb-4">
        <h2 className="text-2xl font-bold mb-4">Panel de Análisis de Gastos</h2>
        
        <form onSubmit={handleSubmit} className="flex flex-col md:flex-row gap-4">
          <div className="flex-1">
            <label htmlFor="fechaInicio" className="block text-sm font-medium text-gray-700 mb-1">
              Fecha Inicio
            </label>
            <div className="relative">
              <Calendar className="absolute left-2 top-2 h-5 w-5 text-gray-400" />
              <input
                type="date"
                id="fechaInicio"
                value={fechaInicio}
                onChange={(e) => setFechaInicio(e.target.value)}
                className="pl-9 w-full p-2 border border-gray-300 rounded-md"
                required
              />
            </div>
          </div>
          
          <div className="flex-1">
            <label htmlFor="fechaFin" className="block text-sm font-medium text-gray-700 mb-1">
              Fecha Fin
            </label>
            <div className="relative">
              <Calendar className="absolute left-2 top-2 h-5 w-5 text-gray-400" />
              <input
                type="date"
                id="fechaFin"
                value={fechaFin}
                onChange={(e) => setFechaFin(e.target.value)}
                className="pl-9 w-full p-2 border border-gray-300 rounded-md"
                required
              />
            </div>
          </div>
          
          <div className="flex items-end">
            <button
              type="submit"
              className="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
              disabled={cargando}
            >
              {cargando ? 'Analizando...' : 'Analizar'}
            </button>
          </div>
        </form>
      </div>
      
      <div className="mb-4 bg-white p-2 rounded-lg shadow">
        <div className="flex overflow-x-auto">
          <button
            onClick={() => cargarDatos('dashboard')}
            className={`flex items-center px-4 py-2 border-b-2 ${
              vistaActiva === 'dashboard' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent hover:border-gray-300'
            }`}
          >
            <BarChart2 className="mr-2 h-5 w-5" />
            Dashboard
          </button>
          
          <button
            onClick={() => cargarDatos('sobre-ventas')}
            className={`flex items-center px-4 py-2 border-b-2 ${
              vistaActiva === 'sobre-ventas' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent hover:border-gray-300'
            }`}
          >
            <DollarSign className="mr-2 h-5 w-5" />
            Gastos sobre Ventas
          </button>
          
          <button
            onClick={() => cargarDatos('sobre-total')}
            className={`flex items-center px-4 py-2 border-b-2 ${
              vistaActiva === 'sobre-total' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent hover:border-gray-300'
            }`}
          >
            <PieChartIcon className="mr-2 h-5 w-5" />
            Distribución de Gastos
          </button>
          
          <button
            onClick={() => cargarDatos('mas-relevantes')}
            className={`flex items-center px-4 py-2 border-b-2 ${
              vistaActiva === 'mas-relevantes' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent hover:border-gray-300'
            }`}
          >
            <BarChart className="mr-2 h-5 w-5" />
            Gastos Más Relevantes
          </button>
        </div>
      </div>
      
      <div className="bg-white rounded-lg shadow">
        {renderContent()}
      </div>
    </div>
  );
};

export default PanelGastosAnalisis;