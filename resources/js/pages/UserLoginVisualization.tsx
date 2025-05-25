import React, { useState, useEffect, useCallback } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, PieChart, Pie, Cell } from 'recharts';
import { Users, Clock, Activity, TrendingUp, Search, Filter, Calendar as CalendarIcon2, Download, RefreshCw } from 'lucide-react';
import { format, addDays, subDays, startOfMonth, endOfMonth, startOfWeek, endOfWeek } from 'date-fns';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

// Type definitions
interface User {
    id: number;
    name: string;
    email: string;
    area_name?: string;
    roles?: string[];
}

interface DateRange {
    start: string;
    end: string;
}

interface CalendarState {
    start: boolean;
    end: boolean;
}

interface DatePreset {
    label: string;
    days?: number;
    start?: Date;
    end?: Date;
}

interface ChartDataPoint {
    date: string;
    week_label?: string;
    week?: number;
    month_short?: string;
    month_label?: string;
    total_hours: number;
    session_count: number;
    label?: string;
    hours?: number;
    sessions?: number;
}

interface OverallStats {
    total_hours: number;
    total_sessions: number;
    avg_session_hours: number;
    active_days: number;
    activity_percentage: number;
}

interface PeakHour {
    hour_label: string;
    total_hours: number;
    login_count: number;
}

interface DayOfWeekActivity {
    day_name: string;
    total_hours: number;
    login_count: number;
}

interface AnalyticsSummary {
    total_hours: number;
    total_sessions: number;
    avg_daily_hours?: number;
    avg_weekly_hours?: number;
    avg_monthly_hours?: number;
    days_analyzed?: number;
    weeks_analyzed?: number;
    months_analyzed?: number;
}

interface AnalyticsData {
    chart_data: ChartDataPoint[];
    summary: AnalyticsSummary;
}

interface DashboardData {
    overall_stats: OverallStats;
    peak_hours: PeakHour[];
    day_of_week_activity: DayOfWeekActivity[];
}

interface PaginationInfo {
    has_more: boolean;
    current_page: number;
}

interface ApiResponse<T> {
    success: boolean;
    data: T;
    pagination?: PaginationInfo;
}

type ViewType = 'daily' | 'weekly' | 'monthly';

const UserLoginAnalyticsDashboard: React.FC = () => {
    // State management
    const [users, setUsers] = useState<User[]>([]);
    const [selectedUser, setSelectedUser] = useState<User | null>(null);
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [usersLoading, setUsersLoading] = useState<boolean>(false);
    const [analyticsLoading, setAnalyticsLoading] = useState<boolean>(false);
    const [hasMoreUsers, setHasMoreUsers] = useState<boolean>(true);
    const [currentPage, setCurrentPage] = useState<number>(1);

    // Analytics data
    const [analyticsData, setAnalyticsData] = useState<AnalyticsData | null>(null);
    const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);

    // Filters and options
    const [dateRange, setDateRange] = useState<DateRange>({
        start: format(subDays(new Date(), 30), 'yyyy-MM-dd'),
        end: format(new Date(), 'yyyy-MM-dd')
    });
    const [viewType, setViewType] = useState<ViewType>('daily');
    const [showCalendar, setShowCalendar] = useState<CalendarState>({ start: false, end: false });

    // Predefined date ranges
    const datePresets: DatePreset[] = [
        { label: 'Last 7 days', days: 7 },
        { label: 'Last 30 days', days: 30 },
        { label: 'Last 90 days', days: 90 },
        { label: 'This month', start: startOfMonth(new Date()), end: endOfMonth(new Date()) },
        { label: 'This week', start: startOfWeek(new Date()), end: endOfWeek(new Date()) }
    ];

    // Colors for charts
    const chartColors: string[] = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6'];

    // Fetch users with infinite scroll
    const fetchUsers = useCallback(async (page: number = 1, search: string = '', reset: boolean = false): Promise<void> => {
        setUsersLoading(true);
        try {
            const response = await fetch(`/api/user-login-analytics/getAllUsers?page=${page}&per_page=20&search=${encodeURIComponent(search)}`);
            const data: ApiResponse<User[]> = await response.json();

            if (data.success) {
                if (reset || page === 1) {
                    setUsers(data.data);
                } else {
                    setUsers(prev => [...prev, ...data.data]);
                }
                if (data.pagination) {
                    setHasMoreUsers(data.pagination.has_more);
                    setCurrentPage(data.pagination.current_page);
                }
            }
        } catch (error) {
            console.error('Error fetching users:', error);
        } finally {
            setUsersLoading(false);
        }
    }, []);

    // Fetch analytics data for selected user
    const fetchAnalyticsData = useCallback(async (userId: number): Promise<void> => {
        if (!userId) return;

        setAnalyticsLoading(true);
        try {
            // Fetch analytics based on view type
            const analyticsResponse = await fetch(
                `/api/user-login-analytics/${viewType}?user_id=${userId}&start_date=${dateRange.start}&end_date=${dateRange.end}`
            );
            const analyticsData: ApiResponse<AnalyticsData> = await analyticsResponse.json();

            // Fetch dashboard data
            const dashboardResponse = await fetch(
                `/api/user-login-analytics/dashboard?user_id=${userId}&start_date=${dateRange.start}&end_date=${dateRange.end}`
            );
            const dashboardData: ApiResponse<DashboardData> = await dashboardResponse.json();

            if (analyticsData.success) {
                setAnalyticsData(analyticsData.data);
            }
            if (dashboardData.success) {
                setDashboardData(dashboardData.data);
            }
        } catch (error) {
            console.error('Error fetching analytics:', error);
        } finally {
            setAnalyticsLoading(false);
        }
    }, [viewType, dateRange]);

    // Initial load and search debounce
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            fetchUsers(1, searchTerm, true);
        }, 300);
        return () => clearTimeout(timeoutId);
    }, [searchTerm, fetchUsers]);

    // Fetch analytics when user or filters change
    useEffect(() => {
        if (selectedUser) {
            fetchAnalyticsData(selectedUser.id);
        }
    }, [selectedUser, fetchAnalyticsData]);

    // Handle infinite scroll
    const handleScroll = (e: React.UIEvent<HTMLDivElement>): void => {
        const target = e.target as HTMLDivElement;
        const { scrollTop, scrollHeight, clientHeight } = target;
        if (scrollHeight - scrollTop === clientHeight && hasMoreUsers && !usersLoading) {
            fetchUsers(currentPage + 1, searchTerm, false);
        }
    };

    // Set date range preset
    const setDatePreset = (preset: DatePreset): void => {
        if (preset.days) {
            setDateRange({
                start: format(subDays(new Date(), preset.days), 'yyyy-MM-dd'),
                end: format(new Date(), 'yyyy-MM-dd')
            });
        } else if (preset.start && preset.end) {
            setDateRange({
                start: format(preset.start, 'yyyy-MM-dd'),
                end: format(preset.end, 'yyyy-MM-dd')
            });
        }
    };

    // Format chart data based on view type
    const getFormattedChartData = (): ChartDataPoint[] => {
        if (!analyticsData?.chart_data) return [];

        return analyticsData.chart_data.map(item => {
            let label = '';
            let safeDate = item.date ? new Date(item.date) : null;

            if (viewType === 'daily') {
                if (!safeDate || isNaN(safeDate.getTime())) {
                    console.warn("Invalid date in chart_data:", item);
                    label = 'Invalid';
                } else {
                    label = format(safeDate, 'MMM dd');
                }
            } else if (viewType === 'weekly') {
                label = item.week_label || `Week ${item.week}`;
            } else if (viewType === 'monthly') {
                label = item.month_short || item.month_label || '';
            }

            return {
                ...item,
                label,
                hours: item.total_hours,
                sessions: item.session_count
            };
        });
    };

    // Export data functionality
    const exportData = (): void => {
        if (!analyticsData) return;

        const dataToExport = {
            user: selectedUser,
            period: { start: dateRange.start, end: dateRange.end, type: viewType },
            analytics: analyticsData,
            dashboard: dashboardData
        };

        const blob = new Blob([JSON.stringify(dataToExport, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `user-analytics-${selectedUser?.name}-${dateRange.start}-${dateRange.end}.json`;
        a.click();
        URL.revokeObjectURL(url);
    };

    const formatDuration = (hours: number): string => {
        if (hours < 1) return `${Math.round(hours * 60)}m`;
        return `${hours.toFixed(1)}h`;
    };

    // Custom tooltip formatter for recharts
    const customTooltipFormatter = (value: any, name: string): [string, string] => {
        if (name === 'hours') {
            return [`${value}h`, 'Hours'];
        }
        return [value.toString(), name === 'sessions' ? 'Sessions' : name];
    };

    // Handle date selection
    const handleDateSelect = (date: Date | undefined, type: 'start' | 'end'): void => {
        if (date) {
            setDateRange(prev => ({ ...prev, [type]: format(date, 'yyyy-MM-dd') }));
            setShowCalendar(prev => ({ ...prev, [type]: false }));
        }
    };

    return (
        <DashboardLayout>
            <div className="flex h-screen bg-gray-50 dark:bg-background">
                {/* Users Sidebar */}
                <div className="w-80 bg-white dark:bg-card border-r border-gray-200 dark:border-border flex flex-col">
                    <div className="p-4 border-b border-gray-200 dark:border-border">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-foreground mb-3">Users</h2>
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-muted-foreground w-4 h-4" />
                            <Input
                                placeholder="Search users..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-9 bg-white dark:bg-background border-gray-200 dark:border-border text-gray-900 dark:text-foreground"
                            />
                        </div>
                    </div>

                    <div
                        className="flex-1 overflow-y-auto"
                        onScroll={handleScroll}
                    >
                        <div className="p-2">
                            {users.map((user) => (
                                <div
                                    key={user.id}
                                    onClick={() => setSelectedUser(user)}
                                    className={`p-3 mb-2 rounded-lg cursor-pointer transition-colors ${selectedUser?.id === user.id
                                        ? 'bg-blue-50 dark:bg-sidebar-accent border-2 border-blue-200 dark:border-sidebar-primary'
                                        : 'hover:bg-gray-50 dark:hover:bg-sidebar-accent border-2 border-transparent'
                                        }`}
                                >
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-gray-900 dark:text-foreground truncate">
                                                {user.name}
                                            </p>
                                            <p className="text-xs text-gray-500 dark:text-muted-foreground truncate">{user.email}</p>
                                            {user.area_name && (
                                                <Badge variant="secondary" className="mt-1 text-xs bg-secondary dark:bg-secondary text-secondary-foreground dark:text-secondary-foreground">
                                                    {user.area_name}
                                                </Badge>
                                            )}
                                        </div>
                                        {selectedUser?.id === user.id && (
                                            <div className="w-2 h-2 bg-blue-500 dark:bg-sidebar-primary rounded-full ml-2" />
                                        )}
                                    </div>
                                    {user.roles && user.roles.length > 0 && (
                                        <div className="mt-2 flex flex-wrap gap-1">
                                            {user.roles.slice(0, 2).map((role, index) => (
                                                <Badge key={index} variant="outline" className="text-xs border-gray-200 dark:border-border text-gray-700 dark:text-muted-foreground">
                                                    {role}
                                                </Badge>
                                            ))}
                                            {user.roles.length > 2 && (
                                                <Badge variant="outline" className="text-xs border-gray-200 dark:border-border text-gray-700 dark:text-muted-foreground">
                                                    +{user.roles.length - 2}
                                                </Badge>
                                            )}
                                        </div>
                                    )}
                                </div>
                            ))}

                            {usersLoading && (
                                <div className="flex justify-center p-4">
                                    <RefreshCw className="w-5 h-5 animate-spin text-gray-400 dark:text-muted-foreground" />
                                </div>
                            )}

                            {!hasMoreUsers && users.length > 0 && (
                                <div className="text-center text-gray-500 dark:text-muted-foreground text-sm p-4">
                                    All users loaded
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 flex flex-col overflow-hidden">
                    {selectedUser ? (
                        <>
                            {/* Header */}
                            <div className="bg-white dark:bg-card border-b border-gray-200 dark:border-border p-6">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h1 className="text-2xl font-bold text-gray-900 dark:text-foreground">
                                            Login Analytics - {selectedUser.name}
                                        </h1>
                                        <p className="text-gray-600 dark:text-muted-foreground">{selectedUser.email}</p>
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <Button onClick={exportData} variant="outline" size="sm" className="border-gray-200 dark:border-border text-gray-900 dark:text-foreground hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                            <Download className="w-4 h-4 mr-2" />
                                            Export
                                        </Button>
                                        <Button
                                            onClick={() => fetchAnalyticsData(selectedUser.id)}
                                            variant="outline"
                                            size="sm"
                                            disabled={analyticsLoading}
                                            className="border-gray-200 dark:border-border text-gray-900 dark:text-foreground hover:bg-gray-50 dark:hover:bg-sidebar-accent"
                                        >
                                            <RefreshCw className={`w-4 h-4 mr-2 ${analyticsLoading ? 'animate-spin' : ''}`} />
                                            Refresh
                                        </Button>
                                    </div>
                                </div>

                                {/* Filters */}
                                <div className="mt-6 flex flex-wrap items-center gap-4">
                                    <div className="flex items-center gap-2">
                                        <label className="text-sm font-medium text-gray-700 dark:text-foreground">Period:</label>
                                        <Select value={viewType} onValueChange={(value: ViewType) => setViewType(value)}>
                                            <SelectTrigger className="w-32 bg-white dark:bg-background border-gray-200 dark:border-border text-gray-900 dark:text-foreground">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent className="bg-white dark:bg-popover border-gray-200 dark:border-border">
                                                <SelectItem value="daily" className="text-gray-900 dark:text-popover-foreground hover:bg-gray-50 dark:hover:bg-accent">Daily</SelectItem>
                                                <SelectItem value="weekly" className="text-gray-900 dark:text-popover-foreground hover:bg-gray-50 dark:hover:bg-accent">Weekly</SelectItem>
                                                <SelectItem value="monthly" className="text-gray-900 dark:text-popover-foreground hover:bg-gray-50 dark:hover:bg-accent">Monthly</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <label className="text-sm font-medium text-gray-700 dark:text-foreground">Quick Select:</label>
                                        <Select onValueChange={(value: string) => {
                                            const preset = datePresets[parseInt(value)];
                                            setDatePreset(preset);
                                        }}>
                                            <SelectTrigger className="w-40 bg-white dark:bg-background border-gray-200 dark:border-border text-gray-900 dark:text-foreground">
                                                <SelectValue placeholder="Select range" />
                                            </SelectTrigger>
                                            <SelectContent className="bg-white dark:bg-popover border-gray-200 dark:border-border">
                                                {datePresets.map((preset, index) => (
                                                    <SelectItem key={index} value={index.toString()} className="text-gray-900 dark:text-popover-foreground hover:bg-gray-50 dark:hover:bg-accent">
                                                        {preset.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Popover open={showCalendar.start} onOpenChange={(open) => setShowCalendar(prev => ({ ...prev, start: open }))}>
                                            <PopoverTrigger asChild>
                                                <Button variant="outline" className="w-40 bg-white dark:bg-background border-gray-200 dark:border-border text-gray-900 dark:text-foreground hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                                    <CalendarIcon2 className="w-4 h-4 mr-2" />
                                                    {format(new Date(dateRange.start), 'MMM dd, yyyy')}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent className="w-auto p-0 bg-white dark:bg-popover border-gray-200 dark:border-border">
                                                <Calendar
                                                    mode="single"
                                                    selected={new Date(dateRange.start)}
                                                    onSelect={(date) => handleDateSelect(date, 'start')}
                                                    initialFocus
                                                    className="text-gray-900 dark:text-popover-foreground"
                                                />
                                            </PopoverContent>
                                        </Popover>

                                        <span className="text-gray-500 dark:text-muted-foreground">to</span>

                                        <Popover open={showCalendar.end} onOpenChange={(open) => setShowCalendar(prev => ({ ...prev, end: open }))}>
                                            <PopoverTrigger asChild>
                                                <Button variant="outline" className="w-40 bg-white dark:bg-background border-gray-200 dark:border-border text-gray-900 dark:text-foreground hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                                    <CalendarIcon2 className="w-4 h-4 mr-2" />
                                                    {format(new Date(dateRange.end), 'MMM dd, yyyy')}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent className="w-auto p-0 bg-white dark:bg-popover border-gray-200 dark:border-border">
                                                <Calendar
                                                    mode="single"
                                                    selected={new Date(dateRange.end)}
                                                    onSelect={(date) => handleDateSelect(date, 'end')}
                                                    initialFocus
                                                    className="text-gray-900 dark:text-popover-foreground"
                                                />
                                            </PopoverContent>
                                        </Popover>
                                    </div>
                                </div>
                            </div>

                            {/* Content */}
                            <div className="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-background">
                                {analyticsLoading ? (
                                    <div className="flex items-center justify-center h-64">
                                        <RefreshCw className="w-8 h-8 animate-spin text-gray-400 dark:text-muted-foreground" />
                                    </div>
                                ) : (
                                    <div className="space-y-6">
                                        {/* Summary Cards */}
                                        {dashboardData?.overall_stats && (
                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                                <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                    <CardContent className="p-6">
                                                        <div className="flex items-center">
                                                            <Clock className="w-8 h-8 text-blue-500 dark:text-dashboard-blue" />
                                                            <div className="ml-4">
                                                                <p className="text-sm font-medium text-gray-600 dark:text-muted-foreground">Total Hours</p>
                                                                <p className="text-2xl font-bold text-gray-900 dark:text-card-foreground">
                                                                    {dashboardData.overall_stats.total_hours}h
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>

                                                <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                    <CardContent className="p-6">
                                                        <div className="flex items-center">
                                                            <Activity className="w-8 h-8 text-green-500 dark:text-dashboard-green" />
                                                            <div className="ml-4">
                                                                <p className="text-sm font-medium text-gray-600 dark:text-muted-foreground">Sessions</p>
                                                                <p className="text-2xl font-bold text-gray-900 dark:text-card-foreground">
                                                                    {dashboardData.overall_stats.total_sessions}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>

                                                <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                    <CardContent className="p-6">
                                                        <div className="flex items-center">
                                                            <TrendingUp className="w-8 h-8 text-orange-500 dark:text-dashboard-amber" />
                                                            <div className="ml-4">
                                                                <p className="text-sm font-medium text-gray-600 dark:text-muted-foreground">Avg Session</p>
                                                                <p className="text-2xl font-bold text-gray-900 dark:text-card-foreground">
                                                                    {formatDuration(dashboardData.overall_stats.avg_session_hours)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>

                                                <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                    <CardContent className="p-6">
                                                        <div className="flex items-center">
                                                            <Users className="w-8 h-8 text-purple-500 dark:text-dashboard-purple" />
                                                            <div className="ml-4">
                                                                <p className="text-sm font-medium text-gray-600 dark:text-muted-foreground">Active Days</p>
                                                                <p className="text-2xl font-bold text-gray-900 dark:text-card-foreground">
                                                                    {dashboardData.overall_stats.active_days}
                                                                </p>
                                                                <p className="text-xs text-gray-500 dark:text-muted-foreground">
                                                                    {dashboardData.overall_stats.activity_percentage}% activity
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            </div>
                                        )}

                                        {/* Main Chart */}
                                        <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                            <CardHeader className="border-b border-gray-200 dark:border-border">
                                                <CardTitle className="text-gray-900 dark:text-card-foreground">Login Time Analysis - {viewType.charAt(0).toUpperCase() + viewType.slice(1)}</CardTitle>
                                            </CardHeader>
                                            <CardContent className="p-6">
                                                <div className="h-80">
                                                    <ResponsiveContainer width="100%" height="100%">
                                                        <LineChart data={getFormattedChartData()}>
                                                            <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" className="dark:stroke-gray-700" />
                                                            <XAxis
                                                                dataKey="label"
                                                                tick={{ fontSize: 12, fill: '#6b7280' }}
                                                                className="dark:fill-muted-foreground"
                                                                angle={-45}
                                                                textAnchor="end"
                                                                height={60}
                                                            />
                                                            <YAxis tick={{ fontSize: 12, fill: '#6b7280' }} className="dark:fill-muted-foreground" />
                                                            <Tooltip
                                                                labelFormatter={(label) => {
                                                                    const dataPoint = getFormattedChartData().find(item => item.label === label);
                                                                    const year = dataPoint?.date ? new Date(dataPoint.date).getFullYear() : new Date().getFullYear();

                                                                    return `Period: ${label}, ${year}`;
                                                                }}
                                                                formatter={customTooltipFormatter}
                                                                contentStyle={{
                                                                    backgroundColor: 'hsl(var(--popover))',
                                                                    border: '1px solid hsl(var(--border))', // has -> hsl 오타 수정
                                                                    borderRadius: '8px',
                                                                    color: 'hsl(var(--popover-foreground))'
                                                                }}
                                                                labelStyle={{ color: 'hsl(var(--popover-foreground))' }}
                                                                itemStyle={{ color: 'hsl(var(--popover-foreground))' }}
                                                            />
                                                            <Line
                                                                type="monotone"
                                                                dataKey="hours"
                                                                stroke="#3B82F6"
                                                                strokeWidth={2}
                                                                dot={{ r: 4 }}
                                                                activeDot={{ r: 6 }}
                                                            />
                                                        </LineChart>
                                                    </ResponsiveContainer>
                                                </div>
                                            </CardContent>
                                        </Card>

                                        {/* Additional Analytics */}
                                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            {/* Peak Hours */}
                                            {dashboardData?.peak_hours && (
                                                <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                    <CardHeader className="border-b border-gray-200 dark:border-border">
                                                        <CardTitle className="text-gray-900 dark:text-card-foreground">Peak Activity Hours</CardTitle>
                                                    </CardHeader>
                                                    <CardContent className="p-6">
                                                        <div className="h-64">
                                                            <ResponsiveContainer width="100%" height="100%">
                                                                <BarChart
                                                                    data={dashboardData.peak_hours}
                                                                    className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
                                                                >
                                                                    <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" className="dark:stroke-gray-700" />
                                                                    <XAxis dataKey="hour_label" tick={{ fontSize: 12, fill: '#6b7280' }} className="dark:fill-muted-foreground" />
                                                                    <YAxis tick={{ fontSize: 12, fill: '#6b7280' }} className="dark:fill-muted-foreground" />
                                                                    <Tooltip
                                                                        formatter={(value: any, name: string) => [
                                                                            name === 'total_hours' ? `${value}h` : value,
                                                                            name === 'total_hours' ? 'Total Hours' : 'Logins'
                                                                        ]}
                                                                        contentStyle={{
                                                                            backgroundColor: 'hsl(var(--popover))',
                                                                            border: '1px solid hsl(var(--border))',
                                                                            borderRadius: '8px',
                                                                            color: 'hsl(var(--popover-foreground))'
                                                                        }}
                                                                        labelStyle={{ color: 'hsl(var(--popover-foreground))' }}
                                                                        itemStyle={{ color: 'hsl(var(--popover-foreground))' }}
                                                                    />
                                                                    <Bar dataKey="total_hours" fill="#10B981" />
                                                                </BarChart>
                                                            </ResponsiveContainer>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            )}

                                            {/* Day of Week Activity */}
                                            {dashboardData?.day_of_week_activity && (
                                                <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                    <CardHeader className="border-b border-gray-200 dark:border-border">
                                                        <CardTitle className="text-gray-900 dark:text-card-foreground">Activity by Day of Week</CardTitle>
                                                    </CardHeader>
                                                    <CardContent className="p-6">
                                                        <div className="h-64">
                                                            <ResponsiveContainer width="100%" height="100%">
                                                                <PieChart>
                                                                    <Pie
                                                                        data={dashboardData.day_of_week_activity}
                                                                        cx="50%"
                                                                        cy="50%"
                                                                        outerRadius={80}
                                                                        fill="#8884d8"
                                                                        dataKey="total_hours"
                                                                        label={({ day_name, total_hours }: DayOfWeekActivity) => `${day_name}: ${total_hours}h`}
                                                                    >
                                                                        {dashboardData.day_of_week_activity.map((entry, index) => (
                                                                            <Cell key={`cell-${index}`} fill={chartColors[index % chartColors.length]} />
                                                                        ))}
                                                                    </Pie>
                                                                    <Tooltip
                                                                        formatter={(value: any) => [`${value}h`, 'Hours']}
                                                                        contentStyle={{
                                                                            backgroundColor: 'hsl(var(--popover))',
                                                                            border: '1px solid hsl(var(--border))',
                                                                            borderRadius: '8px',
                                                                            color: 'hsl(var(--popover-foreground))'
                                                                        }}
                                                                        labelStyle={{ color: 'hsl(var(--popover-foreground))' }}
                                                                        itemStyle={{ color: 'hsl(var(--popover-foreground))' }}
                                                                    />
                                                                </PieChart>
                                                            </ResponsiveContainer>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            )}
                                        </div>

                                        {/* Summary Statistics Table */}
                                        {analyticsData?.summary && (
                                            <Card className="bg-white dark:bg-card border-gray-200 dark:border-border">
                                                <CardHeader className="border-b border-gray-200 dark:border-border">
                                                    <CardTitle className="text-gray-900 dark:text-card-foreground">Summary Statistics</CardTitle>
                                                </CardHeader>
                                                <CardContent className="p-6">
                                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                        <div className="text-center p-4 bg-gray-50 dark:bg-muted rounded-lg">
                                                            <p className="text-sm text-gray-600 dark:text-muted-foreground">Total Hours</p>
                                                            <p className="text-xl font-semibold text-gray-900 dark:text-foreground">{analyticsData.summary.total_hours}h</p>
                                                        </div>
                                                        <div className="text-center p-4 bg-gray-50 dark:bg-muted rounded-lg">
                                                            <p className="text-sm text-gray-600 dark:text-muted-foreground">Avg {viewType.charAt(0).toUpperCase() + viewType.slice(1)} Hours</p>
                                                            <p className="text-xl font-semibold text-gray-900 dark:text-foreground">
                                                                {viewType === 'daily' && analyticsData.summary.avg_daily_hours}
                                                                {viewType === 'weekly' && analyticsData.summary.avg_weekly_hours}
                                                                {viewType === 'monthly' && analyticsData.summary.avg_monthly_hours}h
                                                            </p>
                                                        </div>
                                                        <div className="text-center p-4 bg-gray-50 dark:bg-muted rounded-lg">
                                                            <p className="text-sm text-gray-600 dark:text-muted-foreground">Total Sessions</p>
                                                            <p className="text-xl font-semibold text-gray-900 dark:text-foreground">{analyticsData.summary.total_sessions}</p>
                                                        </div>
                                                        <div className="text-center p-4 bg-gray-50 dark:bg-muted rounded-lg">
                                                            <p className="text-sm text-gray-600 dark:text-muted-foreground">
                                                                {viewType === 'daily' && 'Days'}
                                                                {viewType === 'weekly' && 'Weeks'}
                                                                {viewType === 'monthly' && 'Months'} Analyzed
                                                            </p>
                                                            <p className="text-xl font-semibold text-gray-900 dark:text-foreground">
                                                                {viewType === 'daily' && analyticsData.summary.days_analyzed}
                                                                {viewType === 'weekly' && analyticsData.summary.weeks_analyzed}
                                                                {viewType === 'monthly' && analyticsData.summary.months_analyzed}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        )}
                                    </div>
                                )}
                            </div>
                        </>
                    ) : (
                        <div className="flex-1 flex items-center justify-center bg-gray-50 dark:bg-background">
                            <div className="text-center">
                                <Users className="w-16 h-16 text-gray-400 dark:text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-medium text-gray-900 dark:text-foreground mb-2">
                                    Select a User
                                </h3>
                                <p className="text-gray-600 dark:text-muted-foreground">
                                    Choose a user from the sidebar to view their login analytics
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
};

export default UserLoginAnalyticsDashboard;