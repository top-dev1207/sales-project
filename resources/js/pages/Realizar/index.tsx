import React, { useState } from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import { useToast } from "@/hooks/use-toast";
import { BarChart, Bar, LineChart, Line, PieChart, Pie, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, Cell } from 'recharts';
import SalesWeatherChart from './SalesWeatherChart';
import SalaryDashboard from './SalaryDashboard';
import Proveedores from './Proveedores';
import SalesProjection from './SalesProjection';
import PanelGastosAnalisis from './PanelGastosAnalisis';
import CostMetricsPanel from './CostMetricsPanel';
// import PanelAnalisisComparativo from './PanelAnalisisComparativo';

const Realizar = () => {
    return (
        <DashboardLayout>
            <div className="flex flex-col p-4 bg-gray-50 text-gray-800">
                <h1 className="text-2xl font-bold mb-4">Panel de KPIs</h1>
                <SalesProjection />
                <Proveedores />
                <PanelGastosAnalisis />
                <CostMetricsPanel />
                {/* <SalaryDashboard /> */}
                {/* </div> */}
                {/* <SalesWeatherChart /> */}
            </div>
        </DashboardLayout>
    );
};

export default Realizar;
