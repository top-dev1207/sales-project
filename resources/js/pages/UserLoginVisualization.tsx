import React, { useState, useEffect } from 'react';
import {
    LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
    BarChart, Bar, PieChart, Pie, Cell
} from 'recharts';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

// Tipos de datos
interface LoginSession {
    user_id: number;
    last_login_time: string;
    last_session_duration: number | null;
}

interface LoginStats {
    user_id: number;
    time_period: string;
    login_count: number;
    avg_session_duration: number;
    total_session_duration: number;
}

interface LoginReport {
    period: {
        start_date: string;
        end_date: string;
        group_by: string;
    };
    data: LoginStats[];
}

// Colores para los gráficos
const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d', '#ffc658'];

// Componente principal
const PanelLoginTiempo = () => {
    // Estados
    const [activeTab, setActiveTab] = useState<string>('diario');
    const [userLoginTime, setUserLoginTime] = useState<LoginSession | null>(null);
    const [dailyStats, setDailyStats] = useState<LoginStats[]>([]);
    const [weeklyStats, setWeeklyStats] = useState<LoginStats[]>([]);
    const [monthlyStats, setMonthlyStats] = useState<LoginStats[]>([]);
    const [customReport, setCustomReport] = useState<LoginReport | null>(null);
    const [startDate, setStartDate] = useState<string>(
        new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    );
    const [endDate, setEndDate] = useState<string>(
        new Date().toISOString().split('T')[0]
    );
    const [groupBy, setGroupBy] = useState<string>('day');
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    // Funciones de fetch
    const fetchUserLoginTime = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/user-login-time');
            if (!response.ok) throw new Error('Error al obtener los datos de inicio de sesión');

            const data = await response.json();
            setUserLoginTime(data);
            setError(null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Error desconocido');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const fetchDailyStats = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/login-stats/daily');
            if (!response.ok) throw new Error('Error al obtener estadísticas diarias');

            const data = await response.json();
            setDailyStats(data);
            setError(null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Error desconocido');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const fetchWeeklyStats = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/login-stats/weekly');
            if (!response.ok) throw new Error('Error al obtener estadísticas semanales');

            const data = await response.json();
            setWeeklyStats(data);
            setError(null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Error desconocido');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const fetchMonthlyStats = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/login-stats/monthly');
            if (!response.ok) throw new Error('Error al obtener estadísticas mensuales');

            const data = await response.json();
            setMonthlyStats(data);
            setError(null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Error desconocido');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const fetchCustomReport = async () => {
        try {
            setLoading(true);
            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate,
                group_by: groupBy
            });

            const response = await fetch(`/api/login-report?${params.toString()}`);
            if (!response.ok) throw new Error('Error al obtener el informe personalizado');

            const data = await response.json();
            setCustomReport(data);
            setError(null);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Error desconocido');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    // Efectos para cargar datos
    useEffect(() => {
        fetchUserLoginTime();
    }, []);

    useEffect(() => {
        if (activeTab === 'diario') {
            fetchDailyStats();
        } else if (activeTab === 'semanal') {
            fetchWeeklyStats();
        } else if (activeTab === 'mensual') {
            fetchMonthlyStats();
        } else if (activeTab === 'personalizado') {
            fetchCustomReport();
        }
    }, [activeTab]);

    // Funciones auxiliares
    const formatDuration = (seconds: number | null): string => {
        if (seconds === null) return 'N/A';

        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = Math.floor(seconds % 60);

        return `${hours}h ${minutes}m ${remainingSeconds}s`;
    };

    const formatDate = (dateString: string): string => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    // Transformación de datos para gráficos
    const getChartData = () => {
        let data: any[] = [];

        if (activeTab === 'diario') {
            data = dailyStats.slice(0, 30); // Limitamos a 30 días para mejor visualización
        } else if (activeTab === 'semanal') {
            data = weeklyStats.slice(0, 12); // Limitamos a 12 semanas
        } else if (activeTab === 'mensual') {
            data = monthlyStats.slice(0, 12); // Limitamos a 12 meses
        } else if (activeTab === 'personalizado' && customReport) {
            data = customReport.data.slice(0, 30); // Limitamos a 30 períodos
        }

        return data;
    };

    // Handlers
    const handleTabChange = (tab: string) => {
        setActiveTab(tab);
    };

    const handleCustomReportSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        fetchCustomReport();
    };

    return (
        <DashboardLayout>
            <div className="w-full max-w-6xl mx-auto p-4 bg-white rounded-lg shadow-lg">
                <h1 className="text-2xl font-bold mb-6 text-gray-800">Panel de Control de Tiempos de Sesión</h1>

                {/* Información de última sesión */}
                <div className="bg-blue-50 p-4 rounded-lg mb-6">
                    <h2 className="text-xl font-semibold mb-3 text-blue-800">Tu Última Sesión</h2>
                    {loading && userLoginTime === null ? (
                        <div className="flex items-center justify-center p-8 min-h-[400px] dark:bg-background">
                            <div className="text-center">
                                <div className="w-12 h-12 border-4 border-t-dashboard-blue dark:border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
                                <p className="text-foreground dark:text-foreground">sobreprima.....</p>
                            </div>
                        </div>
                    ) : error ? (
                        <p className="text-red-500">{error}</p>
                    ) : userLoginTime ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="bg-white p-3 rounded-md shadow">
                                <h3 className="font-medium text-gray-700">Último inicio de sesión</h3>
                                <p className="text-xl font-semibold">{userLoginTime.last_login_time ? formatDate(userLoginTime.last_login_time) : 'Sin registro'}</p>
                            </div>
                            <div className="bg-white p-3 rounded-md shadow">
                                <h3 className="font-medium text-gray-700">Duración de la sesión</h3>
                                <p className="text-xl font-semibold">{formatDuration(userLoginTime.last_session_duration)}</p>
                            </div>
                        </div>
                    ) : (
                        <p className="text-gray-600">No hay datos de sesión disponibles</p>
                    )}
                </div>

                {/* Tabs de navegación */}
                <div className="border-b border-gray-200 mb-4">
                    <nav className="flex -mb-px">
                        <button
                            onClick={() => handleTabChange('diario')}
                            className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${activeTab === 'diario'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                        >
                            Estadísticas Diarias
                        </button>
                        <button
                            onClick={() => handleTabChange('semanal')}
                            className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${activeTab === 'semanal'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                        >
                            Estadísticas Semanales
                        </button>
                        <button
                            onClick={() => handleTabChange('mensual')}
                            className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${activeTab === 'mensual'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                        >
                            Estadísticas Mensuales
                        </button>
                        <button
                            onClick={() => handleTabChange('personalizado')}
                            className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${activeTab === 'personalizado'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                        >
                            Informe Personalizado
                        </button>
                    </nav>
                </div>

                {/* Contenido de las pestañas */}
                <div className="mb-8">
                    {activeTab === 'personalizado' && (
                        <form onSubmit={handleCustomReportSubmit} className="bg-gray-50 p-4 rounded-lg mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label htmlFor="startDate" className="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha de inicio
                                </label>
                                <input
                                    type="date"
                                    id="startDate"
                                    value={startDate}
                                    onChange={(e) => setStartDate(e.target.value)}
                                    className="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                />
                            </div>
                            <div>
                                <label htmlFor="endDate" className="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha de fin
                                </label>
                                <input
                                    type="date"
                                    id="endDate"
                                    value={endDate}
                                    onChange={(e) => setEndDate(e.target.value)}
                                    className="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                />
                            </div>
                            <div>
                                <label htmlFor="groupBy" className="block text-sm font-medium text-gray-700 mb-1">
                                    Agrupar por
                                </label>
                                <select
                                    id="groupBy"
                                    value={groupBy}
                                    onChange={(e) => setGroupBy(e.target.value)}
                                    className="w-full p-2 border border-gray-300 rounded-md"
                                >
                                    <option value="day">Día</option>
                                    <option value="week">Semana</option>
                                    <option value="month">Mes</option>
                                </select>
                            </div>
                            <div className="md:col-span-3">
                                <button
                                    type="submit"
                                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md"
                                    disabled={loading}
                                >
                                    {loading ? 'Generando informe...' : 'Generar informe'}
                                </button>
                            </div>
                        </form>
                    )}

                    {loading ? (
                        <div className="flex justify-center items-center h-64">
                            <p className="text-gray-600">Cargando datos...</p>
                        </div>
                    ) : error ? (
                        <div className="bg-red-50 p-4 rounded-lg">
                            <p className="text-red-600">{error}</p>
                        </div>
                    ) : (
                        <>
                            <h2 className="text-xl font-semibold mb-4 text-gray-800">
                                {activeTab === 'diario' && 'Estadísticas de Sesiones Diarias'}
                                {activeTab === 'semanal' && 'Estadísticas de Sesiones Semanales'}
                                {activeTab === 'mensual' && 'Estadísticas de Sesiones Mensuales'}
                                {activeTab === 'personalizado' && 'Informe Personalizado'}
                            </h2>

                            {/* Gráfico de tiempo promedio de sesión */}
                            <div className="bg-white p-4 rounded-lg shadow mb-6">
                                <h3 className="text-lg font-medium mb-4 text-gray-700">Tiempo Promedio de Sesión</h3>
                                <div className="h-64">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <LineChart
                                            data={getChartData()}
                                            margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis dataKey="time_period" />
                                            <YAxis name="Tiempo (segundos)" />
                                            <Tooltip formatter={(value) => [`${Math.round(Number(value))} segundos`, 'Duración']} />
                                            <Legend />
                                            <Line
                                                type="monotone"
                                                dataKey="avg_session_duration"
                                                name="Tiempo promedio de sesión"
                                                stroke="#8884d8"
                                                activeDot={{ r: 8 }}
                                            />
                                        </LineChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>

                            {/* Gráfico de cantidad de inicios de sesión */}
                            <div className="bg-white p-4 rounded-lg shadow mb-6">
                                <h3 className="text-lg font-medium mb-4 text-gray-700">Cantidad de Inicios de Sesión</h3>
                                <div className="h-64">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            data={getChartData()}
                                            margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis dataKey="time_period" />
                                            <YAxis />
                                            <Tooltip />
                                            <Legend />
                                            <Bar dataKey="login_count" name="Inicios de sesión" fill="#82ca9d" />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>

                            {/* Tabla de datos */}
                            <div className="bg-white p-4 rounded-lg shadow">
                                <h3 className="text-lg font-medium mb-4 text-gray-700">Detalles</h3>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Usuario ID
                                                </th>
                                                <th className="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Período
                                                </th>
                                                <th className="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Inicios de sesión
                                                </th>
                                                <th className="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tiempo promedio
                                                </th>
                                                <th className="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tiempo total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {getChartData().slice(0, 10).map((item, index) => (
                                                <tr key={index} className={index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}>
                                                    <td className="py-2 px-4 text-sm text-gray-900">{item.user_id}</td>
                                                    <td className="py-2 px-4 text-sm text-gray-900">{item.time_period}</td>
                                                    <td className="py-2 px-4 text-sm text-gray-900">{item.login_count}</td>
                                                    <td className="py-2 px-4 text-sm text-gray-900">
                                                        {formatDuration(item.avg_session_duration)}
                                                    </td>
                                                    <td className="py-2 px-4 text-sm text-gray-900">
                                                        {formatDuration(item.total_session_duration)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
};

export default PanelLoginTiempo;