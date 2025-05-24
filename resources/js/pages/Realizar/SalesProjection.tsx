import React, { useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, BarChart, Bar, Cell } from 'recharts';
import { Calendar, ArrowUp, ArrowDown, DollarSign, TrendingUp, Target, BarChart2 } from 'lucide-react';

// Define TypeScript interfaces for our data structure
interface MonthlyProjection {
  year: number;
  month: number;
  month_name: string;
  total_days: number;
  days_elapsed: number;
  sales_to_date: number;
  projected_sales: number;
  projection_formula: string;
  objective: number;
  objective_progress: number;
  remaining_days: number;
}

interface MonthlySalesBreakdown {
  month: number;
  month_name: string;
  sales: number;
  objective: number;
  progress: number;
}

interface AnnualProjection {
  year: number;
  months_elapsed: number;
  sales_to_date: number;
  projected_annual_sales: number;
  projection_formula: string;
  annual_objective: number;
  objective_progress: number;
  monthly_breakdown: MonthlySalesBreakdown[];
  remaining_months: number;
}

interface DashboardData {
  current_date: string;
  monthly_projection: MonthlyProjection;
  annual_projection: AnnualProjection;
  summary: {
    monthly_progress: number;
    annual_progress: number;
    monthly_projection_formula: string;
    annual_projection_formula: string;
  }
}

const SalesProjection = () => {
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedYear, setSelectedYear] = useState<number>(new Date().getFullYear());
  const [selectedMonth, setSelectedMonth] = useState<number>(new Date().getMonth() + 1);
  
  // Function to format currency
  const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('es-AR', { 
      style: 'currency', 
      currency: 'ARS',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2 
    }).format(value);
  };

  // Function to format percentage
  const formatPercentage = (value: number): string => {
    return `${value.toFixed(2)}%`;
  };

  useEffect(() => {
    const fetchDashboardData = async () => {
      setLoading(true);
      try {
        const response = await fetch(`/api/sales-projection/dashboard?year=${selectedYear}&month=${selectedMonth}`);
        if (!response.ok) {
          throw new Error('Error al obtener datos de proyección de ventas');
        }
        const data = await response.json();
        setDashboardData(data);
        setError(null);
      } catch (err) {
        if (err instanceof Error) {
          setError(err.message);
        } else {
          setError('Ocurrió un error desconocido');
        }
        setDashboardData(null);
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, [selectedYear, selectedMonth]);

  // Function to determine progress color based on percentage
  const getProgressColor = (percentage: number): string => {
    if (percentage >= 100) return 'text-green-600';
    if (percentage >= 85) return 'text-green-500';
    if (percentage >= 70) return 'text-yellow-500';
    if (percentage >= 50) return 'text-yellow-600';
    return 'text-red-500';
  };

  // Function to get background color for progress bars
  const getProgressBarColor = (percentage: number): string => {
    if (percentage >= 100) return 'bg-green-600';
    if (percentage >= 85) return 'bg-green-500';
    if (percentage >= 70) return 'bg-yellow-500';
    if (percentage >= 50) return 'bg-yellow-600';
    return 'bg-red-500';
  };

  const months = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
  ];

  // Generate array of available years (current - 2 to current + 1)
  const currentYear = new Date().getFullYear();
  const availableYears = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1];

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8 min-h-[400px] dark:bg-background">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-t-dashboard-blue dark:border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-foreground dark:text-foreground">Cargando datos de proyección.....</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 dark:bg-destructive/10 p-4 rounded-lg">
        <h2 className="text-xl font-semibold text-red-700 dark:text-destructive-foreground mb-2">Error</h2>
        <p className="text-red-600 dark:text-destructive-foreground">{error}</p>
        <p className="mt-2 text-red-600 dark:text-destructive-foreground">Por favor intente nuevamente más tarde o contacte al soporte técnico.</p>
      </div>
    );
  }

  if (!dashboardData) {
    return (
      <div className="bg-yellow-50 dark:bg-accent/10 p-4 rounded-lg">
        <h2 className="text-xl font-semibold text-yellow-700 dark:text-accent-foreground mb-2">Sin datos</h2>
        <p className="text-yellow-600 dark:text-muted-foreground">No hay datos disponibles para mostrar.</p>
      </div>
    );
  }

  // Extract data for convenience
  const { monthly_projection, annual_projection, summary } = dashboardData;

  // Prepare chart data for monthly breakdown
  const monthlyBreakdownData = annual_projection.monthly_breakdown.map(item => ({
    ...item,
    name: item.month_name,
    progressPercentage: item.progress
  }));

  return (
    <div className="bg-white dark:bg-card rounded-lg shadow-lg p-4 md:p-6 animate-fade-in">
      {/* Header - Filter Controls */}
      <div className="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-100">Panel de Proyecciones de Ventas</h1>
        
        <div className="flex flex-wrap gap-2">
          <div className="flex items-center">
            <label htmlFor="year-select" className="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Año:</label>
            <select
              id="year-select"
              value={selectedYear}
              onChange={(e) => setSelectedYear(parseInt(e.target.value))}
              className="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-dashboard-blue focus:ring focus:ring-dashboard-blue focus:ring-opacity-50 px-[20px] py-[5px]"
            >
              {availableYears.map(year => (
                <option key={year} value={year}>{year}</option>
              ))}
            </select>
          </div>
          
          <div className="flex items-center">
            <label htmlFor="month-select" className="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Mes:</label>
            <select
              id="month-select"
              value={selectedMonth}
              onChange={(e) => setSelectedMonth(parseInt(e.target.value))}
              className="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-dashboard-blue focus:ring focus:ring-dashboard-blue focus:ring-opacity-50 px-[20px] py-[5px]"
            >
              {months.map((month, index) => (
                <option key={index} value={index + 1}>{month}</option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {/* Monthly Sales to Date */}
        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border-l-4 border-dashboard-blue">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 dark:text-muted-foreground font-medium">Ventas Mes Actual</p>
              <h3 className="text-xl font-bold text-dashboard-blue dark:text-foreground mt-1">
                {formatCurrency(monthly_projection.sales_to_date)}
              </h3>
              <p className="text-xs text-gray-500 dark:text-muted-foreground mt-1">
                {monthly_projection.days_elapsed} de {monthly_projection.total_days} días transcurridos
              </p>
            </div>
            <div className="p-2 bg-blue-100 dark:bg-blue-950/30 rounded-full border dark:border-blue-800/30">
              <Calendar className="w-6 h-6 text-dashboard-blue dark:text-blue-400" />
            </div>
          </div>
        </div>

        {/* Monthly Projection */}
        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border-l-4 border-dashboard-purple dark:border-dashboard-purple">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 dark:text-muted-foreground font-medium">Proyección Mensual</p>
              <h3 className="text-xl font-bold text-dashboard-purple dark:text-foreground mt-1">
                {formatCurrency(monthly_projection.projected_sales)}
              </h3>
              <p className="text-xs text-gray-500 dark:text-muted-foreground mt-1">
                Faltan {monthly_projection.remaining_days} días para fin de mes
              </p>
            </div>
            <div className="p-2 bg-purple-100 dark:bg-purple-950/30 rounded-full border dark:border-purple-800/30">
              <TrendingUp className="w-6 h-6 text-dashboard-purple dark:text-purple-400" />
            </div>
          </div>
        </div>

        {/* Annual Sales to Date */}
        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border-l-4 border-dashboard-teal dark:border-dashboard-teal">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 dark:text-muted-foreground font-medium">Ventas Año Actual</p>
              <h3 className="text-xl font-bold text-dashboard-teal dark:text-foreground mt-1">
                {formatCurrency(annual_projection.sales_to_date)}
              </h3>
              <p className="text-xs text-gray-500 dark:text-muted-foreground mt-1">
                {annual_projection.months_elapsed} de 12 meses transcurridos
              </p>
            </div>
            <div className="p-2 bg-teal-100 dark:bg-teal-950/30 rounded-full border dark:border-teal-800/30">
              <DollarSign className="w-6 h-6 text-dashboard-teal dark:text-teal-400" />
            </div>
          </div>
        </div>

        {/* Annual Projection */}
        <div className="bg-white dark:bg-card rounded-lg shadow dark:shadow-lg p-4 border-l-4 border-dashboard-amber dark:border-dashboard-amber">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 dark:text-muted-foreground font-medium">Proyección Anual</p>
              <h3 className="text-xl font-bold text-dashboard-amber dark:text-foreground mt-1">
                {formatCurrency(annual_projection.projected_annual_sales)}
              </h3>
              <p className="text-xs text-gray-500 dark:text-muted-foreground mt-1">
                Faltan {annual_projection.remaining_months} meses para fin de año
              </p>
            </div>
            <div className="p-2 bg-amber-100 dark:bg-amber-950/30 rounded-full border dark:border-amber-800/30">
              <BarChart2 className="w-6 h-6 text-dashboard-amber dark:text-amber-400" />
            </div>
          </div>
        </div>
      </div>

      {/* Objectives Progress */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {/* Monthly Objective */}
        <div className="bg-card dark:bg-card border border-border dark:border-border rounded-lg shadow p-5">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-semibold text-foreground dark:text-foreground">Objetivo Mensual</h3>
            <div className="p-2 bg-primary/10 dark:bg-primary/20 rounded-full">
              <Target className="w-5 h-5 text-primary dark:text-primary" />
            </div>
          </div>
          
          <div className="mb-4">
            <div className="flex justify-between mb-1">
              <span className="text-sm font-medium text-foreground dark:text-foreground">
                {formatCurrency(monthly_projection.sales_to_date)} de {formatCurrency(monthly_projection.objective)}
              </span>
              <span className={`text-sm font-medium ${getProgressColor(monthly_projection.objective_progress)}`}>
                {formatPercentage(monthly_projection.objective_progress)}
              </span>
            </div>
            <div className="w-full bg-muted dark:bg-muted rounded-full h-2.5">
              <div 
                className={`h-2.5 rounded-full ${getProgressBarColor(monthly_projection.objective_progress)}`} 
                style={{ width: `${Math.min(monthly_projection.objective_progress, 100)}%` }}
              ></div>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm">
            <div className="flex items-center text-muted-foreground dark:text-muted-foreground mb-2 sm:mb-0">
              <Calendar className="w-4 h-4 mr-1" />
              <span>{monthly_projection.month_name} {monthly_projection.year}</span>
            </div>
            <div className={`flex items-center ${monthly_projection.objective_progress >= 100 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'}`}>
              {monthly_projection.objective_progress >= 100 ? 
                <ArrowUp className="w-4 h-4 mr-1" /> : 
                <ArrowDown className="w-4 h-4 mr-1" />
              }
              <span>
                {monthly_projection.objective_progress >= 100 
                  ? "Objetivo alcanzado" 
                  : `Faltan ${formatCurrency(monthly_projection.objective - monthly_projection.sales_to_date)}`}
              </span>
            </div>
          </div>
        </div>

        {/* Annual Objective */}
        <div className="bg-card dark:bg-card border border-border dark:border-border rounded-lg shadow p-5">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-semibold text-foreground dark:text-foreground">Objetivo Anual</h3>
            <div className="p-2 bg-accent/10 dark:bg-accent/20 rounded-full">
              <Target className="w-5 h-5 text-accent dark:text-accent" />
            </div>
          </div>
          
          <div className="mb-4">
            <div className="flex justify-between mb-1">
              <span className="text-sm font-medium text-foreground dark:text-foreground">
                {formatCurrency(annual_projection.sales_to_date)} de {formatCurrency(annual_projection.annual_objective)}
              </span>
              <span className={`text-sm font-medium ${getProgressColor(annual_projection.objective_progress)}`}>
                {formatPercentage(annual_projection.objective_progress)}
              </span>
            </div>
            <div className="w-full bg-muted dark:bg-muted rounded-full h-2.5">
              <div 
                className={`h-2.5 rounded-full ${getProgressBarColor(annual_projection.objective_progress)}`} 
                style={{ width: `${Math.min(annual_projection.objective_progress, 100)}%` }}
              ></div>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm">
            <div className="flex items-center text-muted-foreground dark:text-muted-foreground mb-2 sm:mb-0">
              <Calendar className="w-4 h-4 mr-1" />
              <span>{annual_projection.year}</span>
            </div>
            <div className={`flex items-center ${annual_projection.objective_progress >= 100 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'}`}>
              {annual_projection.objective_progress >= 100 ? 
                <ArrowUp className="w-4 h-4 mr-1" /> : 
                <ArrowDown className="w-4 h-4 mr-1" />
              }
              <span>
                {annual_projection.objective_progress >= 100 
                  ? "Objetivo alcanzado" 
                  : `Faltan ${formatCurrency(annual_projection.annual_objective - annual_projection.sales_to_date)}`}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Charts Section */}
      {/* <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6"> */}
        {/* Monthly Progress Bar Chart */}
       <div className="bg-white dark:bg-card rounded-lg shadow p-5 border dark:border-border">
  <h3 className="text-lg font-semibold text-gray-800 dark:text-foreground mb-4">Progreso Mensual del Año {annual_projection.year}</h3>
  <div className="h-80">
    <ResponsiveContainer width="100%" height="100%">
      <BarChart
        data={monthlyBreakdownData}
        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
      >
        <CartesianGrid 
          strokeDasharray="3 3" 
          stroke="currentColor" 
          className="text-border dark:text-border opacity-30" 
        />
        <XAxis 
          dataKey="name" 
          tick={{ fill: 'currentColor' }}
          axisLine={{ stroke: 'currentColor' }}
          tickLine={{ stroke: 'currentColor' }}
          className="text-muted-foreground"
        />
        <YAxis 
          tick={{ fill: 'currentColor' }}
          axisLine={{ stroke: 'currentColor' }}
          tickLine={{ stroke: 'currentColor' }}
          className="text-muted-foreground"
        />
        <Tooltip
          formatter={(value, name) => {
            if (name === "sales") return [formatCurrency(Number(value)), "Ventas"];
            if (name === "objective") return [formatCurrency(Number(value)), "Objetivo"];
            if (name === "progressPercentage") return [formatPercentage(Number(value)), "Progreso"];
            return [value, name];
          }}
          contentStyle={{ 
            backgroundColor: 'hsl(var(--background))',
            border: '1px solid hsl(var(--border))',
            borderRadius: '6px',
            color: 'hsl(var(--foreground))',
            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
          }}
          labelStyle={{ color: 'hsl(var(--foreground))' }}
        />
        <Legend 
          wrapperStyle={{ color: 'hsl(var(--foreground))' }}
        />
        <Bar dataKey="sales" name="Ventas" fill="#3B82F6" radius={[2, 2, 0, 0]} />
        <Bar dataKey="objective" name="Objetivo" fill="#22C55E" radius={[2, 2, 0, 0]} />
      </BarChart>
    </ResponsiveContainer>
  </div>
</div>
        {/* Trends Line Chart */}
        <div className="bg-card dark:bg-card border border-border dark:border-border rounded-lg shadow p-5">
          <h3 className="text-lg font-semibold text-foreground dark:text-foreground mb-4">Tendencia de Ventas vs Objetivos</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart
                data={monthlyBreakdownData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
              >
                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                <XAxis 
                  dataKey="name" 
                  tick={{ fill: 'hsl(var(--foreground))' }}
                  axisLine={{ stroke: 'hsl(var(--border))' }}
                />
                <YAxis 
                  tick={{ fill: 'hsl(var(--foreground))' }}
                  axisLine={{ stroke: 'hsl(var(--border))' }}
                />
                <Tooltip
                  formatter={(value, name) => {
                    if (name === "sales") return [formatCurrency(Number(value)), "Ventas"];
                    if (name === "objective") return [formatCurrency(Number(value)), "Objetivo"];
                    if (name === "progressPercentage") return [formatPercentage(Number(value)), "Progreso"];
                    return [value, name];
                  }}
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
                <Line type="monotone" dataKey="sales" name="Ventas" stroke="hsl(var(--primary))" strokeWidth={2} />
                <Line type="monotone" dataKey="objective" name="Objetivo" stroke="hsl(var(--accent))" strokeWidth={2} />
                <Line type="monotone" dataKey="progressPercentage" name="Progreso %" stroke="hsl(var(--destructive))" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      {/* </div> */}

      {/* Projection Formulas Information */}
      {/* <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-5 mb-6">
        <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Fórmulas de Proyección</h3>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <h4 className="font-medium text-dashboard-blue mb-2">Proyección Mensual</h4>
            <p className="text-sm text-gray-700 dark:text-gray-300">
              <strong>Fórmula:</strong> (Ventas hasta la fecha / Días transcurridos) × Total de días en el mes
            </p>
            <p className="text-sm text-gray-700 dark:text-gray-300 mt-2">
              <strong>Aplicada:</strong> {monthly_projection.projection_formula}
            </p>
            <p className="text-sm text-gray-700 dark:text-gray-300 mt-2">
              Esta fórmula estima las ventas totales del mes basándose en el promedio diario de ventas hasta la fecha.
            </p>
          </div>
          
          <div className="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">
            <h4 className="font-medium text-dashboard-teal mb-2">Proyección Anual</h4>
            <p className="text-sm text-gray-700 dark:text-gray-300">
              <strong>Fórmula:</strong> {annual_projection.months_elapsed === 1 
                ? "Proyección del primer mes × 12" 
                : "(Ventas hasta la fecha / Meses transcurridos) × 12"}
            </p>
            <p className="text-sm text-gray-700 dark:text-gray-300 mt-2">
              <strong>Aplicada:</strong> {annual_projection.projection_formula}
            </p>
            <p className="text-sm text-gray-700 dark:text-gray-300 mt-2">
              Esta fórmula estima las ventas totales del año basándose en el promedio mensual de ventas hasta la fecha.
            </p>
          </div>
        </div>
      </div> */}

      {/* Data Tables Section */}
      <div className="bg-card dark:bg-card border border-border dark:border-border rounded-lg shadow overflow-hidden">
        <div className="px-5 py-4 border-b border-border dark:border-border">
          <h3 className="text-lg font-semibold text-foreground dark:text-foreground">Detalle Mensual de Ventas vs Objetivos</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-border dark:divide-border">
            <thead className="bg-muted dark:bg-muted">
              <tr>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-muted-foreground dark:text-muted-foreground uppercase tracking-wider">Mes</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-muted-foreground dark:text-muted-foreground uppercase tracking-wider">Ventas</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-muted-foreground dark:text-muted-foreground uppercase tracking-wider">Objetivo</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-muted-foreground dark:text-muted-foreground uppercase tracking-wider">Progreso</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-muted-foreground dark:text-muted-foreground uppercase tracking-wider">Estado</th>
              </tr>
            </thead>
            <tbody className="bg-card dark:bg-card divide-y divide-border dark:divide-border">
              {annual_projection.monthly_breakdown.map((month, index) => (
                <tr key={index} className={selectedMonth === month.month ? "bg-primary/10 dark:bg-primary/20" : ""}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground dark:text-foreground">{month.month_name}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground dark:text-muted-foreground">{formatCurrency(month.sales)}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground dark:text-muted-foreground">{formatCurrency(month.objective)}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground dark:text-muted-foreground">{formatPercentage(month.progress)}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {month.progress >= 100 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                        Completado
                      </span>
                    ) : month.progress >= 85 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                        En Meta
                      </span>
                    ) : month.progress >= 70 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                        En Progreso
                      </span>
                    ) : month.progress > 0 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                        Atrasado
                      </span>
                    ) : (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-muted dark:bg-muted text-muted-foreground dark:text-muted-foreground">
                        Sin Datos
                      </span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default SalesProjection;