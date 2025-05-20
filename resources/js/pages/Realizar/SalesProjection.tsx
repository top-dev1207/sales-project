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
      <div className="flex items-center justify-center p-8 min-h-[400px]">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-t-dashboard-blue rounded-full animate-spin mx-auto mb-4"></div>
          <p>Cargando datos de proyección...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 p-4 rounded-lg">
        <h2 className="text-xl font-semibold text-red-700 mb-2">Error</h2>
        <p className="text-red-600">{error}</p>
        <p className="mt-2">Por favor intente nuevamente más tarde o contacte al soporte técnico.</p>
      </div>
    );
  }

  if (!dashboardData) {
    return (
      <div className="bg-yellow-50 p-4 rounded-lg">
        <h2 className="text-xl font-semibold text-yellow-700 mb-2">Sin datos</h2>
        <p className="text-yellow-600">No hay datos disponibles para mostrar.</p>
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
    <div className="bg-white rounded-lg shadow-lg p-4 md:p-6 animate-fade-in">
      {/* Header - Filter Controls */}
      <div className="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <h1 className="text-2xl font-bold text-gray-800">Panel de Proyecciones de Ventas</h1>
        
        <div className="flex flex-wrap gap-2">
          <div className="flex items-center">
            <label htmlFor="year-select" className="mr-2 text-sm font-medium">Año:</label>
            <select
              id="year-select"
              value={selectedYear}
              onChange={(e) => setSelectedYear(parseInt(e.target.value))}
              className="rounded-md border-gray-300 shadow-sm focus:border-dashboard-blue focus:ring focus:ring-dashboard-blue focus:ring-opacity-50"
            >
              {availableYears.map(year => (
                <option key={year} value={year}>{year}</option>
              ))}
            </select>
          </div>
          
          <div className="flex items-center">
            <label htmlFor="month-select" className="mr-2 text-sm font-medium">Mes:</label>
            <select
              id="month-select"
              value={selectedMonth}
              onChange={(e) => setSelectedMonth(parseInt(e.target.value))}
              className="rounded-md border-gray-300 shadow-sm focus:border-dashboard-blue focus:ring focus:ring-dashboard-blue focus:ring-opacity-50"
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
        <div className="bg-white rounded-lg shadow p-4 border-l-4 border-dashboard-blue">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 font-medium">Ventas Mes Actual</p>
              <h3 className="text-xl font-bold text-dashboard-blue mt-1">
                {formatCurrency(monthly_projection.sales_to_date)}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                {monthly_projection.days_elapsed} de {monthly_projection.total_days} días transcurridos
              </p>
            </div>
            <div className="p-2 bg-blue-100 rounded-full">
              <Calendar className="w-6 h-6 text-dashboard-blue" />
            </div>
          </div>
        </div>

        {/* Monthly Projection */}
        <div className="bg-white rounded-lg shadow p-4 border-l-4 border-dashboard-purple">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 font-medium">Proyección Mensual</p>
              <h3 className="text-xl font-bold text-dashboard-purple mt-1">
                {formatCurrency(monthly_projection.projected_sales)}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                Faltan {monthly_projection.remaining_days} días para fin de mes
              </p>
            </div>
            <div className="p-2 bg-purple-100 rounded-full">
              <TrendingUp className="w-6 h-6 text-dashboard-purple" />
            </div>
          </div>
        </div>

        {/* Annual Sales to Date */}
        <div className="bg-white rounded-lg shadow p-4 border-l-4 border-dashboard-teal">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 font-medium">Ventas Año Actual</p>
              <h3 className="text-xl font-bold text-dashboard-teal mt-1">
                {formatCurrency(annual_projection.sales_to_date)}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                {annual_projection.months_elapsed} de 12 meses transcurridos
              </p>
            </div>
            <div className="p-2 bg-teal-100 rounded-full">
              <DollarSign className="w-6 h-6 text-dashboard-teal" />
            </div>
          </div>
        </div>

        {/* Annual Projection */}
        <div className="bg-white rounded-lg shadow p-4 border-l-4 border-dashboard-amber">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm text-gray-500 font-medium">Proyección Anual</p>
              <h3 className="text-xl font-bold text-dashboard-amber mt-1">
                {formatCurrency(annual_projection.projected_annual_sales)}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                Faltan {annual_projection.remaining_months} meses para fin de año
              </p>
            </div>
            <div className="p-2 bg-amber-100 rounded-full">
              <BarChart2 className="w-6 h-6 text-dashboard-amber" />
            </div>
          </div>
        </div>
      </div>

      {/* Objectives Progress */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {/* Monthly Objective */}
        <div className="bg-white rounded-lg shadow p-5">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-semibold text-gray-800">Objetivo Mensual</h3>
            <div className="p-2 bg-blue-100 rounded-full">
              <Target className="w-5 h-5 text-dashboard-blue" />
            </div>
          </div>
          
          <div className="mb-4">
            <div className="flex justify-between mb-1">
              <span className="text-sm font-medium text-gray-700">
                {formatCurrency(monthly_projection.sales_to_date)} de {formatCurrency(monthly_projection.objective)}
              </span>
              <span className={`text-sm font-medium ${getProgressColor(monthly_projection.objective_progress)}`}>
                {formatPercentage(monthly_projection.objective_progress)}
              </span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2.5">
              <div 
                className={`h-2.5 rounded-full ${getProgressBarColor(monthly_projection.objective_progress)}`} 
                style={{ width: `${Math.min(monthly_projection.objective_progress, 100)}%` }}
              ></div>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm">
            <div className="flex items-center text-gray-500 mb-2 sm:mb-0">
              <Calendar className="w-4 h-4 mr-1" />
              <span>{monthly_projection.month_name} {monthly_projection.year}</span>
            </div>
            <div className={`flex items-center ${monthly_projection.objective_progress >= 100 ? 'text-green-600' : 'text-yellow-600'}`}>
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
        <div className="bg-white rounded-lg shadow p-5">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-semibold text-gray-800">Objetivo Anual</h3>
            <div className="p-2 bg-teal-100 rounded-full">
              <Target className="w-5 h-5 text-dashboard-teal" />
            </div>
          </div>
          
          <div className="mb-4">
            <div className="flex justify-between mb-1">
              <span className="text-sm font-medium text-gray-700">
                {formatCurrency(annual_projection.sales_to_date)} de {formatCurrency(annual_projection.annual_objective)}
              </span>
              <span className={`text-sm font-medium ${getProgressColor(annual_projection.objective_progress)}`}>
                {formatPercentage(annual_projection.objective_progress)}
              </span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2.5">
              <div 
                className={`h-2.5 rounded-full ${getProgressBarColor(annual_projection.objective_progress)}`} 
                style={{ width: `${Math.min(annual_projection.objective_progress, 100)}%` }}
              ></div>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm">
            <div className="flex items-center text-gray-500 mb-2 sm:mb-0">
              <Calendar className="w-4 h-4 mr-1" />
              <span>{annual_projection.year}</span>
            </div>
            <div className={`flex items-center ${annual_projection.objective_progress >= 100 ? 'text-green-600' : 'text-yellow-600'}`}>
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
        <div className="bg-white rounded-lg shadow p-5">
          <h3 className="text-lg font-semibold text-gray-800 mb-4">Progreso Mensual del Año {annual_projection.year}</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={monthlyBreakdownData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
              >
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => {
                    if (name === "sales") return [formatCurrency(Number(value)), "Ventas"];
                    if (name === "objective") return [formatCurrency(Number(value)), "Objetivo"];
                    if (name === "progressPercentage") return [formatPercentage(Number(value)), "Progreso"];
                    return [value, name];
                  }}
                />
                <Legend />
                <Bar dataKey="sales" name="Ventas" fill="#3B82F6" />
                <Bar dataKey="objective" name="Objetivo" fill="#22C55E" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Trends Line Chart */}
        <div className="bg-white rounded-lg shadow p-5">
          <h3 className="text-lg font-semibold text-gray-800 mb-4">Tendencia de Ventas vs Objetivos</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart
                data={monthlyBreakdownData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
              >
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => {
                    if (name === "sales") return [formatCurrency(Number(value)), "Ventas"];
                    if (name === "objective") return [formatCurrency(Number(value)), "Objetivo"];
                    if (name === "progressPercentage") return [formatPercentage(Number(value)), "Progreso"];
                    return [value, name];
                  }}
                />
                <Legend />
                <Line type="monotone" dataKey="sales" name="Ventas" stroke="#3B82F6" strokeWidth={2} />
                <Line type="monotone" dataKey="objective" name="Objetivo" stroke="#22C55E" strokeWidth={2} />
                <Line type="monotone" dataKey="progressPercentage" name="Progreso %" stroke="#F59E0B" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      {/* </div> */}

      {/* Projection Formulas Information */}
      {/* <div className="bg-white rounded-lg shadow p-5 mb-6">
        <h3 className="text-lg font-semibold text-gray-800 mb-4">Fórmulas de Proyección</h3>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="bg-blue-50 p-4 rounded-lg">
            <h4 className="font-medium text-dashboard-blue mb-2">Proyección Mensual</h4>
            <p className="text-sm text-gray-700">
              <strong>Fórmula:</strong> (Ventas hasta la fecha / Días transcurridos) × Total de días en el mes
            </p>
            <p className="text-sm text-gray-700 mt-2">
              <strong>Aplicada:</strong> {monthly_projection.projection_formula}
            </p>
            <p className="text-sm text-gray-700 mt-2">
              Esta fórmula estima las ventas totales del mes basándose en el promedio diario de ventas hasta la fecha.
            </p>
          </div>
          
          <div className="bg-teal-50 p-4 rounded-lg">
            <h4 className="font-medium text-dashboard-teal mb-2">Proyección Anual</h4>
            <p className="text-sm text-gray-700">
              <strong>Fórmula:</strong> {annual_projection.months_elapsed === 1 
                ? "Proyección del primer mes × 12" 
                : "(Ventas hasta la fecha / Meses transcurridos) × 12"}
            </p>
            <p className="text-sm text-gray-700 mt-2">
              <strong>Aplicada:</strong> {annual_projection.projection_formula}
            </p>
            <p className="text-sm text-gray-700 mt-2">
              Esta fórmula estima las ventas totales del año basándose en el promedio mensual de ventas hasta la fecha.
            </p>
          </div>
        </div>
      </div> */}

      {/* Data Tables Section */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="px-5 py-4 border-b">
          <h3 className="text-lg font-semibold text-gray-800">Detalle Mensual de Ventas vs Objetivos</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objetivo</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {annual_projection.monthly_breakdown.map((month, index) => (
                <tr key={index} className={selectedMonth === month.month ? "bg-blue-50" : ""}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{month.month_name}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{formatCurrency(month.sales)}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{formatCurrency(month.objective)}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{formatPercentage(month.progress)}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {month.progress >= 100 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Completado
                      </span>
                    ) : month.progress >= 85 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        En Meta
                      </span>
                    ) : month.progress >= 70 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        En Progreso
                      </span>
                    ) : month.progress > 0 ? (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Atrasado
                      </span>
                    ) : (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
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