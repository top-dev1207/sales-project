import React, { useState, useEffect } from 'react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import DateInput from '@/components/ui/DateInput';
// Tipos para los datos
type PeriodData = {
  period: string;
  total: number;
  ventas_fiscal: number;
  ventas_no_fiscal: number;
  venta_alimentos: number;
  venta_bebidas: number;
};

type PaymentMethod = {
  id: number;
  name: string;
  total: number;
};

type Target = {
  year: number;
  month: number;
  month_name: string;
  target: number;
  actual: number;
  difference: number;
  achievement_percentage: number;
  description: string;
};

type DashboardData = {
  summary: {
    today_sales: number;
    yesterday_sales: number;
    daily_change: number;
    monthly_sales: number;
    monthly_target: number;
    month_progress: number;
    days_left_in_month: number;
  };
  recent_sales: PeriodData[];
  payment_methods: PaymentMethod[];
};

type CategorySales = {
  food: {
    total: number;
    data: { period: string; total: number }[];
  };
  beverages: {
    total: number;
    data: { period: string; total: number }[];
  };
};

type ComparisonData = {
  current_period: {
    start_date: string;
    end_date: string;
    granularity: string;
    total: number;
  };
  previous_period: {
    start_date: string;
    end_date: string;
    granularity: string;
    total: number;
  };
  growth_percentage: number;
  current_data: PeriodData[];
  previous_data: PeriodData[];
};

// Colores para los gráficos
const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#FF6B6B', '#6BD5FF', '#54D454'];

// Formatear números como moneda
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    minimumFractionDigits: 0
  }).format(value);
};

// Formatear fechas
const formatDate = (dateStr: string) => {
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat('es-AR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(date);
};

// Componente de tarjeta para métricas
const MetricCard: React.FC<{
  title: string;
  value: string | number;
  subvalue?: string | number;
  icon?: string;
  trend?: number;
}> = ({ title, value, subvalue, trend }) => {
  return (
    <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 flex flex-col border dark:border-border">
      <div className="text-gray-500 dark:text-muted-foreground text-sm mb-1">{title}</div>
      <div className="text-2xl font-semibold mb-1 text-foreground dark:text-card-foreground">{value}</div>
      {subvalue && <div className="text-sm text-gray-600 dark:text-muted-foreground">{subvalue}</div>}
      {trend !== undefined && (
        <div className={`text-sm mt-2 flex items-center ${trend >= 0
          ? 'text-green-500 dark:text-green-400'
          : 'text-red-500 dark:text-red-400'
          }`}>
          {trend >= 0 ? '↑' : '↓'} {Math.abs(trend)}%
        </div>
      )}
    </div>
  );
};

// Componente para el progreso de objetivos
const ProgressBar: React.FC<{ progress: number }> = ({ progress }) => {
  const width = Math.min(100, Math.max(0, progress));
  return (
    <div className="w-full bg-gray-200 dark:bg-muted rounded-full h-4 mt-2 border dark:border-border">
      <div
        className={`h-4 rounded-full transition-all duration-300 ${width < 70
          ? 'bg-yellow-500 dark:bg-yellow-400'
          : 'bg-green-500 dark:bg-green-400'
          }`}
        style={{ width: `${width}%` }}
      ></div>
    </div>
  );
};

// Componente del panel principal
const PanelVentas: React.FC = () => {
  // Estados para los diferentes datos
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [view, setView] = useState<string>('dashboard');
  const [period, setPeriod] = useState<string>('month');
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [salesData, setSalesData] = useState<PeriodData[]>([]);
  const [categorySales, setCategorySales] = useState<CategorySales | null>(null);
  const [targetsData, setTargetsData] = useState<Target[]>([]);
  const [comparisonData, setComparisonData] = useState<ComparisonData | null>(null);
  const [startDate, setStartDate] = useState<string>(() => {
    const date = new Date();
    date.setMonth(date.getMonth() - 1);
    return date.toISOString().split('T')[0];
  });
  const [endDate, setEndDate] = useState<string>(() => {
    return new Date().toISOString().split('T')[0];
  });

  // Función para cargar datos desde la API
  const fetchData = async (endpoint: string, params = {}) => {
    setLoading(true);
    setError(null);

    try {
      // Construir URL con parámetros de consulta
      const queryParams = new URLSearchParams();
      Object.entries(params).forEach(([key, value]) => {
        queryParams.append(key, String(value));
      });

      const url = `/api/sales-analytics/${endpoint}?${queryParams.toString()}`;

      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          // Asumimos que el token de autenticación está en localStorage
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.ok) {
        throw new Error(`Error de API: ${response.status}`);
      }

      const data = await response.json();
      return data;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Error desconocido');
      console.error('Error fetching data:', err);
      return null;
    } finally {
      setLoading(false);
    }
  };

  // Cargar datos del dashboard
  const loadDashboard = async () => {
    const data = await fetchData('dashboard');
    if (data?.status === 'success') {
      setDashboardData(data);
    }
  };

  // Cargar datos de ventas por período
  const loadSalesData = async () => {
    const data = await fetchData(period, { start_date: startDate, end_date: endDate });
    if (data?.status === 'success') {
      setSalesData(data.data);
    }
  };

  // Cargar datos de ventas por categoría
  const loadCategorySales = async () => {
    const data = await fetchData('by-category', { start_date: startDate, end_date: endDate });
    if (data?.status === 'success') {
      setCategorySales(data.categories);
    }
  };

  // Cargar datos de objetivos
  const loadTargets = async () => {
    const data = await fetchData('targets', { year: new Date().getFullYear() });
    if (data?.status === 'success') {
      setTargetsData(data.data);
    }
  };

  // Cargar datos de comparación
  const loadComparison = async () => {
    const data = await fetchData('comparison', { period, count: 12 });
    if (data?.status === 'success') {
      setComparisonData(data);
    }
  };

  // Efecto para cargar datos iniciales
  useEffect(() => {
    if (view === 'dashboard') {
      loadDashboard();
    } else if (view === 'sales') {
      loadSalesData();
    } else if (view === 'categories') {
      loadCategorySales();
    } else if (view === 'targets') {
      loadTargets();
    } else if (view === 'comparison') {
      loadComparison();
    }
  }, [view, period, startDate, endDate]);

  // Manejadores para cambios en los filtros
  const handlePeriodChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setPeriod(e.target.value);
  };

  const handleViewChange = (newView: string) => {
    setView(newView);
  };

  // Componente de navegación
  const Navigation = () => (
    <div className="bg-white dark:bg-card rounded-lg shadow-lg p-4 md:p-6 animate-fade-in border dark:border-border">
      <h1 className="text-2xl font-bold mb-4 text-foreground dark:text-card-foreground">Panel Analítico de Ventas</h1>
      <div className="flex flex-wrap gap-4 text-white dark:text-card-foreground">
        <button
          onClick={() => handleViewChange('dashboard')}
          className={`px-4 py-2 rounded transition-colors ${view === 'dashboard'
            ? 'bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground'
            : 'bg-gray-700 dark:bg-muted text-white dark:text-muted-foreground hover:bg-gray-600 dark:hover:bg-muted/80'
            }`}
        >
          Dashboard
        </button>
        <button
          onClick={() => handleViewChange('sales')}
          className={`px-4 py-2 rounded transition-colors ${view === 'sales'
            ? 'bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground'
            : 'bg-gray-700 dark:bg-muted text-white dark:text-muted-foreground hover:bg-gray-600 dark:hover:bg-muted/80'
            }`}
        >
          Ventas por Período
        </button>
        <button
          onClick={() => handleViewChange('categories')}
          className={`px-4 py-2 rounded transition-colors ${view === 'categories'
            ? 'bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground'
            : 'bg-gray-700 dark:bg-muted text-white dark:text-muted-foreground hover:bg-gray-600 dark:hover:bg-muted/80'
            }`}
        >
          Ventas por Categoría
        </button>
        <button
          onClick={() => handleViewChange('targets')}
          className={`px-4 py-2 rounded transition-colors ${view === 'targets'
            ? 'bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground'
            : 'bg-gray-700 dark:bg-muted text-white dark:text-muted-foreground hover:bg-gray-600 dark:hover:bg-muted/80'
            }`}
        >
          Objetivos de Ventas
        </button>
        <button
          onClick={() => handleViewChange('comparison')}
          className={`px-4 py-2 rounded transition-colors ${view === 'comparison'
            ? 'bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground'
            : 'bg-gray-700 dark:bg-muted text-white dark:text-muted-foreground hover:bg-gray-600 dark:hover:bg-muted/80'
            }`}
        >
          Comparativa
        </button>
      </div>
    </div>
  );
  // Componente de filtros
  const Filters = () => (
    <div className="bg-gray-100 dark:bg-muted p-4 mb-4 rounded-lg border dark:border-border">
      <div className="flex flex-wrap gap-4 items-center">
        {view !== 'dashboard' && (
          <>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-muted-foreground mb-1">Desde</label>
              <DateInput
              // className="p-2 border dark:border-border rounded bg-white dark:bg-card text-foreground dark:text-card-foreground focus:ring-2 focus:ring-primary dark:focus:ring-primary focus:border-transparent"
              value={startDate}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setStartDate(e.target.value)}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-muted-foreground mb-1">Hasta</label>
              <DateInput
              // className="p-2 border dark:border-border rounded bg-white dark:bg-card text-foreground dark:text-card-foreground focus:ring-2 focus:ring-primary dark:focus:ring-primary focus:border-transparent"
              value={endDate}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEndDate(e.target.value)}
              />
            </div>
          </>
        )}

        {(view === 'sales' || view === 'comparison') && (
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-muted-foreground mb-1">Período</label>
            <select
              className="p-2 border dark:border-border rounded bg-white dark:bg-card text-foreground dark:text-card-foreground focus:ring-2 focus:ring-primary dark:focus:ring-primary focus:border-transparent"
              value={period}
              onChange={handlePeriodChange}
            >
              <option value="day">Diario</option>
              <option value="week">Semanal</option>
              <option value="month">Mensual</option>
            </select>
          </div>
        )}

        <button
          className="bg-blue-600 dark:bg-primary text-white dark:text-primary-foreground px-4 py-2 rounded mt-4 md:mt-0 hover:bg-blue-700 dark:hover:bg-primary/90 focus:ring-2 focus:ring-blue-500 dark:focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-background transition-colors"
          onClick={() => {
            if (view === 'dashboard') loadDashboard();
            else if (view === 'sales') loadSalesData();
            else if (view === 'categories') loadCategorySales();
            else if (view === 'targets') loadTargets();
            else if (view === 'comparison') loadComparison();
          }}
        >
          Actualizar
        </button>
      </div>
    </div>
  );

  // Componente de visualización del dashboard
  const Dashboard = () => {
    if (!dashboardData) return null;

    const { summary, recent_sales, payment_methods } = dashboardData;

    return (
      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <MetricCard
            title="Ventas de Hoy"
            value={formatCurrency(summary.today_sales)}
            trend={summary.daily_change}
          />
          <MetricCard
            title="Ventas de Ayer"
            value={formatCurrency(summary.yesterday_sales)}
          />
          <MetricCard
            title="Ventas Mensuales"
            value={formatCurrency(summary.monthly_sales)}
            subvalue={`Objetivo: ${formatCurrency(summary.monthly_target)}`}
          />
          <MetricCard
            title="Progreso Mensual"
            value={`${summary.month_progress.toFixed(1)}%`}
            subvalue={`${summary.days_left_in_month} días restantes`}
          />
        </div>

        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
          <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">Progreso hacia el objetivo</h2>
          <ProgressBar progress={summary.month_progress} />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
            <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">Ventas Recientes</h2>
            <div className="h-80">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart
                  data={recent_sales}
                  margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                >
                  <CartesianGrid
                    strokeDasharray="3 3"
                    className="stroke-gray-200 dark:stroke-border"
                  />
                  <XAxis
                    dataKey="period"
                    className="fill-gray-600 dark:fill-muted-foreground"
                  />
                  <YAxis
                    className="fill-gray-600 dark:fill-muted-foreground"
                  />
                  <Tooltip
                    formatter={(value) => formatCurrency(Number(value))}
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: '6px',
                      color: 'hsl(var(--card-foreground))'
                    }}
                  />
                  <Legend
                    wrapperStyle={{
                      color: 'hsl(var(--foreground))'
                    }}
                  />
                  <Line
                    type="monotone"
                    dataKey="total"
                    stroke="hsl(var(--primary))"
                    name="Ventas Totales"
                    strokeWidth={2}
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
            <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">Métodos de Pago</h2>
            <div className="h-80">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={payment_methods}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                    outerRadius={80}
                    fill="hsl(var(--primary))"
                    dataKey="total"
                    nameKey="name"
                  >
                    {payment_methods.map((entry, index) => (
                      <Cell
                        key={`cell-${index}`}
                        fill={`hsl(${220 + (index * 40)} 70% ${50 + (index * 10)}%)`}
                      />
                    ))}
                  </Pie>
                  <Tooltip
                    formatter={(value) => formatCurrency(Number(value))}
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: '6px',
                      color: 'hsl(var(--card-foreground))'
                    }}
                  />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>
      </div>
    );
  };

  // Componente para visualizar ventas por período
  const SalesByPeriod = () => {
    return (
      <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
        <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">
          Ventas por {period === 'day' ? 'Día' : period === 'week' ? 'Semana' : 'Mes'}
        </h2>
        <div className="h-96">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart
              data={salesData}
              margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
            >
              <CartesianGrid
                strokeDasharray="3 3"
                className="stroke-gray-200 dark:stroke-border"
              />
              <XAxis
                dataKey="period"
                className="fill-gray-600 dark:fill-muted-foreground"
              />
              <YAxis
                className="fill-gray-600 dark:fill-muted-foreground"
              />
              <Tooltip
                formatter={(value) => formatCurrency(Number(value))}
                contentStyle={{
                  backgroundColor: 'hsl(var(--card))',
                  border: '1px solid hsl(var(--border))',
                  borderRadius: '6px',
                  color: 'hsl(var(--card-foreground))'
                }}
              />
              <Legend
                wrapperStyle={{
                  color: 'hsl(var(--foreground))'
                }}
              />
              <Line
                type="monotone"
                dataKey="total"
                stroke="hsl(var(--primary))"
                name="Ventas Totales"
                strokeWidth={2}
              />
              <Line
                type="monotone"
                dataKey="ventas_fiscal"
                stroke="hsl(var(--accent))"
                name="Ventas Fiscales"
                strokeWidth={2}
              />
              <Line
                type="monotone"
                dataKey="ventas_no_fiscal"
                stroke="hsl(var(--destructive))"
                name="Ventas No Fiscales"
                strokeWidth={2}
              />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>
    );
  };

  // Componente para visualizar ventas por categoría
  const SalesByCategory = () => {
    if (!categorySales) return null;

    // Combinar datos para gráfico
    const combinedData = categorySales.food.data.map(foodItem => {
      const beverageItem = categorySales.beverages.data.find(
        bevItem => bevItem.period === foodItem.period
      ) || { period: foodItem.period, total: 0 };

      return {
        period: foodItem.period,
        alimentos: foodItem.total,
        bebidas: beverageItem.total,
        total: foodItem.total + beverageItem.total
      };
    });

    return (
      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <MetricCard
            title="Total Ventas de Alimentos"
            value={formatCurrency(categorySales.food.total)}
          />
          <MetricCard
            title="Total Ventas de Bebidas"
            value={formatCurrency(categorySales.beverages.total)}
          />
        </div>

        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
          <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">Ventas por Categoría</h2>
          <div className="h-96">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={combinedData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
              >
                <CartesianGrid
                  strokeDasharray="3 3"
                  className="stroke-gray-200 dark:stroke-border"
                />
                <XAxis
                  dataKey="period"
                  className="fill-gray-600 dark:fill-muted-foreground"
                />
                <YAxis
                  className="fill-gray-600 dark:fill-muted-foreground"
                />
                <Tooltip
                  formatter={(value) => formatCurrency(Number(value))}
                  contentStyle={{
                    backgroundColor: 'hsl(var(--card))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '6px',
                    color: 'hsl(var(--card-foreground))'
                  }}
                />
                <Legend
                  wrapperStyle={{
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Bar dataKey="alimentos" fill="hsl(var(--primary))" name="Alimentos" />
                <Bar dataKey="bebidas" fill="hsl(var(--accent))" name="Bebidas" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    );
  };

  // Componente para visualizar objetivos de ventas
  const SalesTargets = () => {
    return (
      <div className="space-y-6">
        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
          <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">Objetivos de Ventas Mensuales</h2>
          <div className="h-96">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={targetsData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
              >
                <CartesianGrid
                  strokeDasharray="3 3"
                  className="stroke-gray-200 dark:stroke-border"
                />
                <XAxis
                  dataKey="month_name"
                  className="fill-gray-600 dark:fill-muted-foreground"
                />
                <YAxis
                  className="fill-gray-600 dark:fill-muted-foreground"
                />
                <Tooltip
                  formatter={(value) => formatCurrency(Number(value))}
                  labelFormatter={(label) => `Mes: ${label}`}
                  contentStyle={{
                    backgroundColor: 'hsl(var(--card))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '6px',
                    color: 'hsl(var(--card-foreground))'
                  }}
                />
                <Legend
                  wrapperStyle={{
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Bar dataKey="target" fill="hsl(var(--primary))" name="Objetivo" />
                <Bar dataKey="actual" fill="hsl(var(--accent))" name="Real" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="overflow-x-auto bg-white dark:bg-card rounded-lg shadow dark:shadow-lg border dark:border-border">
          <table className="w-full border-collapse">
            <thead>
              <tr className="bg-gray-200 dark:bg-muted">
                <th className="p-2 text-left text-gray-800 dark:text-muted-foreground">Mes</th>
                <th className="p-2 text-right text-gray-800 dark:text-muted-foreground">Objetivo</th>
                <th className="p-2 text-right text-gray-800 dark:text-muted-foreground">Real</th>
                <th className="p-2 text-right text-gray-800 dark:text-muted-foreground">Diferencia</th>
                <th className="p-2 text-right text-gray-800 dark:text-muted-foreground">% Cumplimiento</th>
              </tr>
            </thead>
            <tbody>
              {targetsData.map((target, index) => (
                <tr key={index} className="border-b border-gray-200 dark:border-border hover:bg-gray-50 dark:hover:bg-muted/50 transition-colors">
                  <td className="p-2 text-foreground dark:text-card-foreground">{target.month_name}</td>
                  <td className="p-2 text-right text-foreground dark:text-card-foreground">{formatCurrency(target.target)}</td>
                  <td className="p-2 text-right text-foreground dark:text-card-foreground">{formatCurrency(target.actual)}</td>
                  <td className={`p-2 text-right ${target.difference >= 0
                    ? 'text-green-600 dark:text-green-400'
                    : 'text-red-600 dark:text-red-400'
                    }`}>
                    {formatCurrency(target.difference)}
                  </td>
                  <td className={`p-2 text-right ${target.achievement_percentage >= 100
                    ? 'text-green-600 dark:text-green-400'
                    : target.achievement_percentage >= 90
                      ? 'text-yellow-600 dark:text-yellow-400'
                      : 'text-red-600 dark:text-red-400'
                    }`}>
                    {target.achievement_percentage.toFixed(1)}%
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    );
  };

  // Componente para visualizar comparativa
  const SalesComparison = () => {
    if (!comparisonData) return null;

    // Procesamiento de datos para gráfico combinado
    const combinedData = comparisonData.current_data.map((current, index) => {
      const previous = comparisonData.previous_data[index] || { period: current.period, total: 0 };

      return {
        period: current.period,
        actual: current.total,
        anterior: previous.total
      };
    });

    return (
      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <MetricCard
            title="Período Actual"
            value={formatCurrency(comparisonData.current_period.total)}
            subvalue={`${formatDate(comparisonData.current_period.start_date)} - ${formatDate(comparisonData.current_period.end_date)}`}
          />
          <MetricCard
            title="Período Anterior"
            value={formatCurrency(comparisonData.previous_period.total)}
            subvalue={`${formatDate(comparisonData.previous_period.start_date)} - ${formatDate(comparisonData.previous_period.end_date)}`}
          />
          <MetricCard
            title="Crecimiento"
            value={`${comparisonData.growth_percentage.toFixed(2)}%`}
            trend={comparisonData.growth_percentage}
          />
        </div>

        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border dark:border-border">
          <h2 className="text-lg font-semibold mb-4 text-foreground dark:text-card-foreground">Comparativa de Ventas</h2>
          <div className="h-96">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart
                data={combinedData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
              >
                <CartesianGrid
                  strokeDasharray="3 3"
                  className="stroke-gray-200 dark:stroke-border"
                />
                <XAxis
                  dataKey="period"
                  className="fill-gray-600 dark:fill-muted-foreground"
                />
                <YAxis
                  className="fill-gray-600 dark:fill-muted-foreground"
                />
                <Tooltip
                  formatter={(value) => formatCurrency(Number(value))}
                  contentStyle={{
                    backgroundColor: 'hsl(var(--card))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '6px',
                    color: 'hsl(var(--card-foreground))'
                  }}
                />
                <Legend
                  wrapperStyle={{
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Line
                  type="monotone"
                  dataKey="actual"
                  stroke="hsl(var(--primary))"
                  name="Período Actual"
                  strokeWidth={2}
                />
                <Line
                  type="monotone"
                  dataKey="anterior"
                  stroke="hsl(var(--accent))"
                  name="Período Anterior"
                  strokeDasharray="3 3"
                  strokeWidth={2}
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-background">
      <Navigation />

      <div className="p-4 bg-white dark:bg-card rounded-lg shadow-lg p-4 md:p-6 animate-fade-in mt-[50px] border dark:border-border" >
        <Filters />

        {loading ? (
          <div className="flex items-center justify-center p-8 min-h-[400px] dark:bg-background">
            <div className="text-center">
              <div className="w-12 h-12 border-4 border-t-dashboard-blue dark:border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
              <p className="text-foreground dark:text-foreground">sobreprima.....</p>
            </div>
          </div>
        ) : error ? (
          <div className="bg-red-100 dark:bg-destructive/10 border border-red-400 dark:border-destructive text-red-700 dark:text-destructive-foreground px-4 py-3 rounded">
            <p>Error: {error}</p>
          </div>
        ) : (
          <div>
            {view === 'dashboard' && <Dashboard />}
            {view === 'sales' && <SalesByPeriod />}
            {view === 'categories' && <SalesByCategory />}
            {view === 'targets' && <SalesTargets />}
            {view === 'comparison' && <SalesComparison />}
          </div>
        )}
      </div>
    </div>
  );
};

export default PanelVentas;