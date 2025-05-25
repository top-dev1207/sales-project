import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { AlertCircle, Calendar, RefreshCw, DollarSign, BarChart2, PieChart as PieChartIcon } from 'lucide-react';
import DateInput from '@/components/ui/DateInput';

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
          <p className="text-foreground dark:text-foreground">No hay datos disponibles para mostrar.</p>
        </div>
      );
    }

    const data = dashboard.data as RubroGasto[];
    data.sort((a, b) => (b.porcentaje_ventas || 0) - (a.porcentaje_ventas || 0));

    return (
      <div className="mt-4">
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Gastos como porcentaje de ventas totales</h3>
        <div className="mb-2">
          <p className="text-sm text-muted-foreground dark:text-muted-foreground">Ventas totales: {formatoMoneda(dashboard.ventas_totales || 0)}</p>
        </div>
        <div className="h-96 bg-card dark:bg-card border border-border dark:border-border rounded-lg p-2">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              data={data}
              layout="vertical"
              margin={{ top: 20, right: 30, left: 150, bottom: 5 }}
              className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
            >
              <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
              <XAxis
                type="number"
                domain={[0, Math.max(...data.map(d => d.porcentaje_ventas || 0)) * 1.1]}
                unit="%"
                tick={{ fill: 'hsl(var(--foreground))' }}
                axisLine={{ stroke: 'hsl(var(--border))' }}
              />
              <YAxis
                dataKey="rubro_nombre"
                type="category"
                width={140}
                tick={{ fill: 'hsl(var(--foreground))' }}
                axisLine={{ stroke: 'hsl(var(--border))' }}
              />
              <Tooltip
                formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                labelFormatter={(label) => `Rubro: ${label}`}
                contentStyle={{
                  backgroundColor: 'hsl(var(--popover))',
                  border: '1px solid hsl(var(--border))',
                  borderRadius: '8px',
                  color: 'hsl(var(--foreground))'
                }}
              />
              <Legend
                wrapperStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Bar dataKey="porcentaje_ventas" name="% de Ventas" fill="hsl(var(--primary))" />
            </BarChart>
          </ResponsiveContainer>
        </div>
        <div className="mt-4 overflow-x-auto">
          <table className="min-w-full bg-card dark:bg-card border border-border dark:border-border rounded-lg">
            <thead className="bg-muted dark:bg-muted">
              <tr>
                <th className="py-2 px-4 border-b border-border dark:border-border text-left text-foreground dark:text-foreground">Rubro</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">Importe</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">% de Ventas</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => (
                <tr key={index} className={index % 2 === 0 ? 'bg-muted/50 dark:bg-muted/50' : 'bg-card dark:bg-card'}>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-foreground dark:text-foreground">{item.rubro_nombre}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">{formatoMoneda(item.importe)}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">{item.porcentaje_ventas?.toFixed(2)}%</td>
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
          <p className="text-foreground dark:text-foreground">No hay datos disponibles para mostrar.</p>
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
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Distribución de gastos por rubro</h3>
        <div className="mb-2">
          <p className="text-sm text-muted-foreground dark:text-muted-foreground">Gastos totales: {formatoMoneda(dashboard.gastos_totales || 0)}</p>
        </div>
        <div className="flex flex-col md:flex-row gap-4">
          <div className="w-full md:w-1/2 h-80 bg-card dark:bg-card border border-border dark:border-border rounded-lg p-2">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={pieData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="hsl(var(--primary))"
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
                  contentStyle={{
                    backgroundColor: 'hsl(var(--popover))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '8px',
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Legend
                  wrapperStyle={{ color: 'hsl(var(--foreground))' }}
                />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="w-full md:w-1/2 h-80 bg-card dark:bg-card border border-border dark:border-border rounded-lg p-2">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={data.slice(0, 8)} // Mostramos solo los 8 más relevantes
                layout="vertical"
                margin={{ top: 20, right: 30, left: 150, bottom: 5 }}
                className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
              >
                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                <XAxis
                  type="number"
                  unit="%"
                  tick={{ fill: 'hsl(var(--foreground))' }}
                  axisLine={{ stroke: 'hsl(var(--border))' }}
                />
                <YAxis
                  dataKey="rubro_nombre"
                  type="category"
                  width={140}
                  tick={{ fill: 'hsl(var(--foreground))' }}
                  axisLine={{ stroke: 'hsl(var(--border))' }}
                />
                <Tooltip
                  formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                  labelFormatter={(label) => `Rubro: ${label}`}
                  contentStyle={{
                    backgroundColor: 'hsl(var(--popover))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '8px',
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Legend
                  wrapperStyle={{ color: 'hsl(var(--foreground))' }}
                />
                <Bar dataKey="porcentaje_gastos_totales" name="% del Total" fill="hsl(var(--accent))" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className="mt-4 overflow-x-auto">
          <table className="min-w-full bg-card dark:bg-card border border-border dark:border-border rounded-lg">
            <thead className="bg-muted dark:bg-muted">
              <tr>
                <th className="py-2 px-4 border-b border-border dark:border-border text-left text-foreground dark:text-foreground">Rubro</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">Importe</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">% del Total</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => (
                <tr key={index} className={index % 2 === 0 ? 'bg-muted/50 dark:bg-muted/50' : 'bg-card dark:bg-card'}>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-foreground dark:text-foreground">{item.rubro_nombre}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">{formatoMoneda(item.importe)}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">{item.porcentaje_gastos_totales?.toFixed(2)}%</td>
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
          <p className="text-foreground dark:text-foreground">No hay datos disponibles para mostrar.</p>
        </div>
      );
    }

    const data = dashboard.data as RubroGasto[];

    return (
      <div className="mt-4">
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Rubros de gastos más relevantes</h3>
        <div className="mb-2">
          <p className="text-sm text-muted-foreground dark:text-muted-foreground">Gastos totales: {formatoMoneda(dashboard.gastos_totales || 0)}</p>
        </div>
        <div className="h-80 bg-card dark:bg-card border border-border dark:border-border rounded-lg p-2">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              data={data}
              margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
              className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
            >
              <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
              <XAxis
                dataKey="rubro_nombre"
                tick={{ fill: 'hsl(var(--foreground))' }}
                axisLine={{ stroke: 'hsl(var(--border))' }}
              />
              <YAxis
                tick={{ fill: 'hsl(var(--foreground))' }}
                axisLine={{ stroke: 'hsl(var(--border))' }}
              />
              <Tooltip
                formatter={(value: any, name: string) => [
                  name === "importe" ? formatoMoneda(value) : `${value.toFixed(2)}%`,
                  name === "importe" ? "Importe" : "% del Total"
                ]}
                contentStyle={{
                  backgroundColor: 'hsl(var(--popover))',
                  border: '1px solid hsl(var(--border))',
                  borderRadius: '8px',
                  color: 'hsl(var(--foreground))'
                }}
              />
              <Legend
                wrapperStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Bar dataKey="importe" name="Importe" fill="hsl(var(--primary))" />
              <Bar dataKey="porcentaje_gastos_totales" name="% del Total" fill="hsl(var(--accent))" />
            </BarChart>
          </ResponsiveContainer>
        </div>
        <div className="mt-4 overflow-x-auto">
          <table className="min-w-full bg-card dark:bg-card border border-border dark:border-border rounded-lg">
            <thead className="bg-muted dark:bg-muted">
              <tr>
                <th className="py-2 px-4 border-b border-border dark:border-border text-center text-foreground dark:text-foreground">#</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-left text-foreground dark:text-foreground">Rubro</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">Importe</th>
                <th className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">% del Total</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => (
                <tr key={index} className={index % 2 === 0 ? 'bg-muted/50 dark:bg-muted/50' : 'bg-card dark:bg-card'}>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-center text-foreground dark:text-foreground">{index + 1}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-foreground dark:text-foreground">{item.rubro_nombre}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">{formatoMoneda(item.importe)}</td>
                  <td className="py-2 px-4 border-b border-border dark:border-border text-right text-foreground dark:text-foreground">{item.porcentaje_gastos_totales?.toFixed(2)}%</td>
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
          <p className="text-foreground dark:text-foreground">No hay datos disponibles para mostrar en el dashboard.</p>
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
          <div className="bg-white dark:bg-card p-4 rounded-lg shadow dark:shadow-lg">
            <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Resumen General</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-blue-50 dark:bg-blue-950/20 p-3 rounded-lg border dark:border-blue-800/30">
                <p className="text-sm text-blue-500 dark:text-blue-400">Ventas Totales</p>
                <p className="text-xl font-bold text-foreground dark:text-foreground">{formatoMoneda(dashboard.ventas_totales || 0)}</p>
              </div>
              <div className="bg-red-50 dark:bg-red-950/20 p-3 rounded-lg border dark:border-red-800/30">
                <p className="text-sm text-red-500 dark:text-red-400">Gastos Totales</p>
                <p className="text-xl font-bold text-foreground dark:text-foreground">{formatoMoneda(dashboard.gastos_totales || 0)}</p>
              </div>
              <div className="bg-green-50 dark:bg-green-950/20 p-3 rounded-lg border dark:border-green-800/30">
                <p className="text-sm text-green-500 dark:text-green-400">Rentabilidad</p>
                <p className="text-xl font-bold text-foreground dark:text-foreground">
                  {formatoMoneda((dashboard.ventas_totales || 0) - (dashboard.gastos_totales || 0))}
                </p>
              </div>
              <div className="bg-purple-50 dark:bg-purple-950/20 p-3 rounded-lg border dark:border-purple-800/30">
                <p className="text-sm text-purple-500 dark:text-purple-400">Margen</p>
                <p className="text-xl font-bold text-foreground dark:text-foreground">
                  {(((dashboard.ventas_totales || 0) - (dashboard.gastos_totales || 0)) / (dashboard.ventas_totales || 1) * 100).toFixed(2)}%
                </p>
              </div>
            </div>
          </div>

          <div className="bg-white dark:bg-card p-4 rounded-lg shadow dark:shadow-lg">
            <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Período de Análisis</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-gray-50 dark:bg-muted p-3 rounded-lg">
                <p className="text-sm text-gray-500 dark:text-muted-foreground">Fecha Inicio</p>
                <p className="text-lg font-semibold text-foreground dark:text-foreground">{formatoFecha(dashboard.periodo.fecha_inicio)}</p>
              </div>
              <div className="bg-gray-50 dark:bg-muted p-3 rounded-lg">
                <p className="text-sm text-gray-500 dark:text-muted-foreground">Fecha Fin</p>
                <p className="text-lg font-semibold text-foreground dark:text-foreground">{formatoFecha(dashboard.periodo.fecha_fin)}</p>
              </div>
            </div>
            <div className="mt-4">
              <p className="text-sm text-gray-500 dark:text-muted-foreground">
                El análisis muestra los gastos clasificados por rubro, permitiendo identificar
                patrones y áreas de oportunidad para la optimización de recursos.
              </p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="bg-white dark:bg-card p-4 rounded-lg shadow dark:shadow-lg">
            <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Top 5 Gastos sobre Ventas</h3>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart
                  data={topGastosSobreVentas}
                  layout="vertical"
                  margin={{ top: 5, right: 30, left: 100, bottom: 5 }}
                  className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
                >
                  <CartesianGrid strokeDasharray="3 3" stroke="currentColor" className="text-border dark:text-border opacity-30" />
                  <XAxis type="number" unit="%" stroke="currentColor" className="text-muted-foreground dark:text-muted-foreground" />
                  <YAxis dataKey="rubro_nombre" type="category" width={90} stroke="currentColor" className="text-muted-foreground dark:text-muted-foreground" />
                  <Tooltip
                    formatter={(value: any) => [`${value.toFixed(2)}%`, 'Porcentaje']}
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: '6px',
                      color: 'hsl(var(--foreground))'
                    }}
                  />
                  <Bar dataKey="porcentaje_ventas" name="% de Ventas" fill="#8884d8" />
                </BarChart>
              </ResponsiveContainer>
            </div>
            <div className="text-right mt-2">
              <button
                onClick={() => cargarDatos('sobre-ventas')}
                className="text-blue-500 dark:text-primary hover:text-blue-700 dark:hover:text-primary/80 text-sm transition-colors"
              >
                Ver análisis completo →
              </button>
            </div>
          </div>

          <div className="bg-white dark:bg-card p-4 rounded-lg shadow dark:shadow-lg">
            <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Gastos Más Relevantes</h3>
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
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: '6px',
                      color: 'hsl(var(--foreground))'
                    }}
                    itemStyle={{ color: 'hsl(var(--foreground))' }}
                    labelStyle={{ color: 'hsl(var(--foreground))' }}
                  />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="text-right mt-2">
              <button
                onClick={() => cargarDatos('mas-relevantes')}
                className="text-blue-500 dark:text-primary hover:text-blue-700 dark:hover:text-primary/80 text-sm transition-colors"
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
          <RefreshCw className="animate-spin h-10 w-10 text-blue-500 dark:text-primary mx-auto mb-4" />
          <p className="text-foreground dark:text-foreground">Cargando datos...</p>
        </div>
      );
    }

    if (error) {
      return (
        <div className="text-center p-10 text-red-500 dark:text-destructive">
          <AlertCircle className="h-10 w-10 mx-auto mb-4" />
          <p>{error}</p>
        </div>
      );
    }

    if (!dashboard) {
      return (
        <div className="text-center p-10">
          <p className="text-foreground dark:text-foreground">Seleccione un período y haga clic en "Analizar" para ver los resultados.</p>
        </div>
      );
    }

    if (dashboard.status === 'warning') {
      return (
        <div className="text-center p-10 text-yellow-500 dark:text-yellow-400">
          <AlertCircle className="h-10 w-10 mx-auto mb-4" />
          <p>{dashboard.message || 'No hay datos disponibles para el período seleccionado.'}</p>
        </div>
      );
    }

    switch (vistaActiva) {
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
    <div className="bg-white dark:bg-card rounded-lg shadow-lg p-4 md:p-6 animate-fade-in shadow mt-[50px]">
      <div className="mb-4">
        <h2 className="text-2xl font-bold mb-4 text-foreground dark:text-foreground">Panel de Análisis de Gastos</h2>

        <form onSubmit={handleSubmit} className="flex flex-col md:flex-row gap-4">
          <div className="flex-1">
            <label htmlFor="fechaInicio" className="block text-sm font-medium text-gray-700 dark:text-muted-foreground mb-1">
              Fecha Inicio
            </label>
            <div className="relative">
              <DateInput
              id="fechaInicio"
              value={fechaInicio}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFechaInicio(e.target.value)}
              // className="pl-9 w-full p-2 border border-gray-300 dark:border-border rounded-md bg-white dark:bg-input text-foreground dark:text-foreground"
              />
            </div>
          </div>

          <div className="flex-1">
            <label htmlFor="fechaFin" className="block text-sm font-medium text-gray-700 dark:text-muted-foreground mb-1">
              Fecha Fin
            </label>
            <div className="relative">
              <DateInput
              id="fechaFin"
              value={fechaFin}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFechaFin(e.target.value)}
              // className="pl-9 w-full p-2 border border-gray-300 dark:border-border rounded-md bg-white dark:bg-input text-foreground dark:text-foreground"
              />
            </div>
          </div>

          <div className="flex items-end">
            <button
              type="submit"
              className="w-full md:w-auto px-4 py-2 bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground rounded-md hover:bg-blue-700 dark:hover:bg-primary/90 transition"
              disabled={cargando}
            >
              {cargando ? 'Analizando...' : 'Analizar'}
            </button>
          </div>
        </form>
      </div>

      <div className="mb-4 bg-white dark:bg-card p-2 rounded-lg shadow dark:shadow-lg">
        <div className="flex overflow-x-auto">
          <button
            onClick={() => cargarDatos('dashboard')}
            className={`flex items-center px-4 py-2 border-b-2 ${vistaActiva === 'dashboard'
              ? 'border-blue-500 dark:border-primary text-blue-600 dark:text-primary'
              : 'border-transparent hover:border-gray-300 dark:hover:border-border text-foreground dark:text-foreground'
              }`}
          >
            <BarChart2 className="mr-2 h-5 w-5" />
            Dashboard
          </button>

          <button
            onClick={() => cargarDatos('sobre-ventas')}
            className={`flex items-center px-4 py-2 border-b-2 ${vistaActiva === 'sobre-ventas'
              ? 'border-blue-500 dark:border-primary text-blue-600 dark:text-primary'
              : 'border-transparent hover:border-gray-300 dark:hover:border-border text-foreground dark:text-foreground'
              }`}
          >
            <DollarSign className="mr-2 h-5 w-5" />
            Gastos sobre Ventas
          </button>

          <button
            onClick={() => cargarDatos('sobre-total')}
            className={`flex items-center px-4 py-2 border-b-2 ${vistaActiva === 'sobre-total'
              ? 'border-blue-500 dark:border-primary text-blue-600 dark:text-primary'
              : 'border-transparent hover:border-gray-300 dark:hover:border-border text-foreground dark:text-foreground'
              }`}
          >
            <PieChartIcon className="mr-2 h-5 w-5" />
            Distribución de Gastos
          </button>

          <button
            onClick={() => cargarDatos('mas-relevantes')}
            className={`flex items-center px-4 py-2 border-b-2 ${vistaActiva === 'mas-relevantes'
              ? 'border-blue-500 dark:border-primary text-blue-600 dark:text-primary'
              : 'border-transparent hover:border-gray-300 dark:hover:border-border text-foreground dark:text-foreground'
              }`}
          >
            <BarChart className="mr-2 h-5 w-5" />
            Gastos Más Relevantes
          </button>
        </div>
      </div>

      <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg">
        {renderContent()}
      </div>
    </div>
  );
};

export default PanelGastosAnalisis;