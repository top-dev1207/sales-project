import React, { useState, useEffect } from 'react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Clock, Calendar, User, LogOut } from 'lucide-react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

// Type definitions
interface UserType {
    id: number;
    name: string;
    email: string;
    department: string;
}

interface DailyLoginData {
    date: string;
    dayOfWeek: string;
    dateWithDay: string; // New field to combine date and day
    loginTime: string;
    logoutTime: string;
    rawLoginTime: Date;
    rawLogoutTime: Date;
    hoursLogged: number;
}

interface WeeklyLoginData {
    week: string;
    weekStartDate: string;
    totalHours: number;
}

interface MonthlyLoginData {
    month: string;
    year: number;
    totalHours: number;
}

interface UserLoginData {
    daily: DailyLoginData[];
    weekly: WeeklyLoginData[];
    monthly: MonthlyLoginData[];
}

type UserDataRecord = Record<number, UserLoginData>;

// Sample user data
const users: UserType[] = [
    { id: 1, name: "Alice Smith", email: "alice@example.com", department: "Engineering" },
    { id: 2, name: "Bob Johnson", email: "bob@example.com", department: "Marketing" },
    { id: 3, name: "Carol Williams", email: "carol@example.com", department: "Sales" },
    { id: 4, name: "David Brown", email: "david@example.com", department: "HR" },
    { id: 5, name: "Eva Davis", email: "eva@example.com", department: "Engineering" }
];

// Generate random login data for each user
const generateLoginData = (userId: number): UserLoginData => {
    // Random login data for the past 30 days
    const dailyData: DailyLoginData[] = [];
    const now = new Date();

    for (let i = 29; i >= 0; i--) {
        const date = new Date(now);
        date.setDate(date.getDate() - i);

        // Random login time between 7am and 10am
        const loginHour = 7 + Math.floor(Math.random() * 3);
        const loginMinute = Math.floor(Math.random() * 60);

        // Random session duration between 7 and 9 hours
        const sessionHours = 7 + Math.floor(Math.random() * 2);
        const sessionMinutes = Math.floor(Math.random() * 60);

        const loginTime = new Date(date);
        loginTime.setHours(loginHour, loginMinute, 0);

        const logoutTime = new Date(loginTime);
        logoutTime.setHours(loginTime.getHours() + sessionHours, loginTime.getMinutes() + sessionMinutes);

        // Total hours logged in
        const hoursLogged = sessionHours + (sessionMinutes / 60);

        // Format day of week to be short like 'Mon'
        const dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'short' });
        // Format date short like '5/13'
        const shortDate = `${date.getMonth() + 1}/${date.getDate()}`;
        // Combined date with day - using line break for straight alignment on chart
        const dateWithDay = `${shortDate}\n${dayOfWeek}`;

        dailyData.push({
            date: date.toLocaleDateString(),
            dayOfWeek,
            dateWithDay, // New field with combined date and day
            loginTime: loginTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            logoutTime: logoutTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            rawLoginTime: loginTime,
            rawLogoutTime: logoutTime,
            hoursLogged: parseFloat(hoursLogged.toFixed(2))
        });
    }

    // Weekly data - aggregate by week
    const weeklyData: WeeklyLoginData[] = [];
    let currentWeek = 0;
    let weeklyHours = 0;
    let weekStartDate = '';

    dailyData.forEach((day, index) => {
        const dayDate = new Date(day.rawLoginTime);
        const weekNum = Math.floor(index / 7);

        if (weekNum !== currentWeek) {
            weeklyData.push({
                week: `Week ${currentWeek + 1}`,
                weekStartDate: weekStartDate,
                totalHours: parseFloat(weeklyHours.toFixed(2))
            });
            currentWeek = weekNum;
            weeklyHours = 0;
            weekStartDate = day.date;
        }

        if (index === 0) {
            weekStartDate = day.date;
        }

        weeklyHours += day.hoursLogged;

        // Add the last week
        if (index === dailyData.length - 1) {
            weeklyData.push({
                week: `Week ${currentWeek + 1}`,
                weekStartDate: weekStartDate,
                totalHours: parseFloat(weeklyHours.toFixed(2))
            });
        }
    });

    // Monthly data
    const monthlyData: MonthlyLoginData[] = [];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Create 6 months of data
    for (let i = 5; i >= 0; i--) {
        const monthDate = new Date(now);
        monthDate.setMonth(monthDate.getMonth() - i);

        // Random hours between 140 and 180 hours per month
        const monthHours = 140 + Math.floor(Math.random() * 40);

        monthlyData.push({
            month: months[monthDate.getMonth()],
            year: monthDate.getFullYear(),
            totalHours: monthHours
        });
    }

    return {
        daily: dailyData,
        weekly: weeklyData,
        monthly: monthlyData
    };
};

// Pregenerate data for all users
const userData: UserDataRecord = users.reduce<UserDataRecord>((acc, user) => {
    acc[user.id] = generateLoginData(user.id);
    return acc;
}, {});

// Time view type
type TimeViewType = 'daily' | 'weekly' | 'monthly';

// Custom X Axis Tick for displaying date in a straight line
const CustomXAxisTick = (props: any) => {
    const { x, y, payload } = props;
    return (
        <g transform={`translate(${x},${y})`}>
            <text
                x={0}
                y={0}
                dy={16}
                textAnchor="middle"
                fill="#666"
                fontSize={11}
            >
                {payload.value}
            </text>
        </g>
    );
};

// Main component
const UserLoginVisualization: React.FC = () => {
    const [selectedUser, setSelectedUser] = useState<UserType | null>(null);
    const [timeView, setTimeView] = useState<TimeViewType>('daily'); // daily, weekly, monthly

    const handleUserSelect = (user: UserType): void => {
        setSelectedUser(user);
        setTimeView('daily');
    };

    return (
        <DashboardLayout>
            <div className="flex flex-col h-screen bg-gray-50">
                <header className="bg-indigo-600 text-white p-4 shadow-md">
                    <h1 className="text-2xl font-bold flex items-center">
                        <Clock className="mr-2" /> User Login Time Dashboard
                    </h1>
                </header>

                <div className="flex flex-1 overflow-hidden">
                    {/* User list sidebar */}
                    <div className="w-64 bg-white shadow-md overflow-y-auto">
                        <div className="p-4 border-b">
                            <h2 className="text-lg font-semibold flex items-center">
                                <User className="mr-2" /> Users
                            </h2>
                        </div>
                        <ul>
                            {users.map(user => (
                                <li
                                    key={user.id}
                                    className={`p-3 border-b hover:bg-gray-100 cursor-pointer ${selectedUser?.id === user.id ? 'bg-indigo-50 border-l-4 border-indigo-500' : ''}`}
                                    onClick={() => handleUserSelect(user)}
                                >
                                    <div className="font-medium">{user.name}</div>
                                    <div className="text-sm text-gray-500">{user.department}</div>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Main content area */}
                    <div className="flex-1 overflow-y-auto p-6">
                        {selectedUser ? (
                            <div>
                                <div className="mb-6">
                                    <h2 className="text-2xl font-bold mb-2">{selectedUser.name}</h2>
                                    <p className="text-gray-600">{selectedUser.email} • {selectedUser.department}</p>

                                    {/* Time view tabs */}
                                    <div className="flex mt-4 border-b">
                                        <button
                                            className={`px-4 py-2 font-medium ${timeView === 'daily' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500'}`}
                                            onClick={() => setTimeView('daily')}
                                        >
                                            Daily
                                        </button>
                                        <button
                                            className={`px-4 py-2 font-medium ${timeView === 'weekly' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500'}`}
                                            onClick={() => setTimeView('weekly')}
                                        >
                                            Weekly
                                        </button>
                                        <button
                                            className={`px-4 py-2 font-medium ${timeView === 'monthly' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500'}`}
                                            onClick={() => setTimeView('monthly')}
                                        >
                                            Monthly
                                        </button>
                                    </div>
                                </div>

                                {/* Charts based on the selected time view */}
                                {timeView === 'daily' && (
                                    <div>
                                        <div className="mb-8">
                                            <h3 className="text-lg font-semibold mb-4">Daily Hours Logged (Last 30 Days)</h3>
                                            <ResponsiveContainer width="100%" height={300}>
                                                <LineChart data={userData[selectedUser.id].daily}>
                                                    <CartesianGrid strokeDasharray="3 3" />
                                                    <XAxis
                                                        dataKey="dateWithDay"
                                                        tick={<CustomXAxisTick />}
                                                        height={60}
                                                        interval={3} // Show every 4th label to prevent crowding
                                                    />
                                                    <YAxis />
                                                    <Tooltip
                                                        formatter={(value: number, name: string) => [`${value} hours`, 'Hours Logged']}
                                                        labelFormatter={(label: string) => label.replace('\n', ', ')}
                                                    />
                                                    <Legend />
                                                    <Line
                                                        type="monotone"
                                                        dataKey="hoursLogged"
                                                        stroke="#4f46e5"
                                                        name="Hours Logged"
                                                        strokeWidth={2}
                                                        dot={{ r: 3 }}
                                                        activeDot={{ r: 6 }}
                                                    />
                                                </LineChart>
                                            </ResponsiveContainer>
                                        </div>

                                        <div>
                                            <h3 className="text-lg font-semibold mb-4">Login & Logout Details</h3>
                                            <div className="relative overflow-hidden h-96"> {/* Fixed height container */}
                                                <div className="overflow-y-auto h-full"> {/* Scrollable div */}
                                                    <table className="min-w-full border rounded-lg">
                                                        <thead className="bg-gray-500 sticky top-0 z-10"> {/* Sticky header */}
                                                            <tr>
                                                                <th className="py-3 px-4 text-left">Date</th>
                                                                <th className="py-3 px-4 text-left">Day</th>
                                                                <th className="py-3 px-4 text-left">Login Time</th>
                                                                <th className="py-3 px-4 text-left">Logout Time</th>
                                                                <th className="py-3 px-4 text-left">Hours</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {userData[selectedUser.id].daily.slice().reverse().map((day, index) => (
                                                                <tr key={index} >
                                                                    <td className="py-3 px-4 border-b">{day.date}</td>
                                                                    <td className="py-3 px-4 border-b">{day.dayOfWeek}</td>
                                                                    <td className="py-3 px-4 border-b font-medium text-indigo-600">{day.loginTime}</td>
                                                                    <td className="py-3 px-4 border-b font-medium text-red-500">{day.logoutTime}</td>
                                                                    <td className="py-3 px-4 border-b">{day.hoursLogged}</td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {timeView === 'weekly' && (
                                    <div>
                                        <h3 className="text-lg font-semibold mb-4">Weekly Hours (Last 4 Weeks)</h3>
                                        <ResponsiveContainer width="100%" height={400}>
                                            <BarChart data={userData[selectedUser.id].weekly}>
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis dataKey="week" />
                                                <YAxis />
                                                <Tooltip
                                                    formatter={(value: number, name: string) => [`${value} hours`, 'Total Hours']}
                                                    labelFormatter={(label: string, payload: any[]) => {
                                                        if (payload.length > 0) {
                                                            return `${label} (Starting ${payload[0].payload.weekStartDate})`;
                                                        }
                                                        return label;
                                                    }}
                                                />
                                                <Legend />
                                                <Bar dataKey="totalHours" fill="#4f46e5" name="Total Hours" />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                )}

                                {timeView === 'monthly' && (
                                    <div>
                                        <h3 className="text-lg font-semibold mb-4">Monthly Hours (Last 6 Months)</h3>
                                        <ResponsiveContainer width="100%" height={400}>
                                            <BarChart data={userData[selectedUser.id].monthly}>
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis
                                                    dataKey="month"
                                                    tickFormatter={(value: string, index: number) => {
                                                        const monthData = userData[selectedUser.id].monthly[index];
                                                        return `${value} ${monthData.year}`;
                                                    }}
                                                />
                                                <YAxis />
                                                <Tooltip
                                                    formatter={(value: number, name: string) => [`${value} hours`, 'Total Hours']}
                                                    labelFormatter={(label: string, payload: any[]) => {
                                                        if (payload.length > 0) {
                                                            return `${label} ${payload[0].payload.year}`;
                                                        }
                                                        return label;
                                                    }}
                                                />
                                                <Legend />
                                                <Bar dataKey="totalHours" fill="#4f46e5" name="Total Hours" />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="h-full flex items-center justify-center text-gray-500">
                                <div className="text-center">
                                    <User size={48} className="mx-auto mb-4 text-gray-400" />
                                    <h3 className="text-xl font-medium mb-2">No User Selected</h3>
                                    <p>Select a user from the list to view their login time data</p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default UserLoginVisualization;
