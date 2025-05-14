
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart as BarChartIcon } from 'lucide-react';
import { 
  Bar, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  ResponsiveContainer,
  BarChart as RechartsBarChart
} from 'recharts';
import { useDashboard } from '@/context/DashboardContext';
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';

// Mock data
const getDailyData = () => [
  { name: 'Mon', sales: 4000 },
  { name: 'Tue', sales: 3500 },
  { name: 'Wed', sales: 5000 },
  { name: 'Thu', sales: 4500 },
  { name: 'Fri', sales: 6500 },
  { name: 'Sat', sales: 8500 },
  { name: 'Sun', sales: 7000 },
];

const getWeeklyData = () => [
  { name: 'Week 1', sales: 25000 },
  { name: 'Week 2', sales: 28000 },
  { name: 'Week 3', sales: 31000 },
  { name: 'Week 4', sales: 29000 },
];

const getMonthlyData = () => [
  { name: 'Jan', sales: 98000 },
  { name: 'Feb', sales: 85000 },
  { name: 'Mar', sales: 110000 },
  { name: 'Apr', sales: 105000 },
  { name: 'May', sales: 125000 },
  { name: 'Jun', sales: 135000 },
];

const config = {
  sales: {
    label: "Sales",
    color: "#3B82F6"
  }
};

const SalesChart = () => {
  const { dateRange } = useDashboard();
  
  const getData = () => {
    switch (dateRange) {
      case 'daily':
        return getDailyData();
      case 'weekly':
        return getWeeklyData();
      case 'monthly':
        return getMonthlyData();
      default:
        return getDailyData();
    }
  };
  
  return (
    <Card className="h-full">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-base font-medium">Sales Overview</CardTitle>
        <BarChartIcon className="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent className="pt-4">
        <div className="h-[350px]">
          <ChartContainer config={config} className="w-full">
            <RechartsBarChart
              data={getData()}
              margin={{
                top: 5,
                right: 5,
                left: 5,
                bottom: 5,
              }}
              className="animate-fade-in"
            >
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e5e7eb" />
              <XAxis 
                dataKey="name" 
                tickLine={false}
                axisLine={{ stroke: "#e5e7eb" }}
              />
              <YAxis 
                tickFormatter={(value) => `$${value}`} 
                width={60}
                tickLine={false}
                axisLine={{ stroke: "#e5e7eb" }}
              />
              <ChartTooltip
                content={({ active, payload }) => {
                  if (!active || !payload) return null;
                  return (
                    <ChartTooltipContent
                      className="bg-white/95 shadow-lg border border-border/40 backdrop-blur-sm"
                      formatter={(value) => [`$${value.toLocaleString()}`, 'Sales']}
                    />
                  );
                }}
              />
              <Bar 
                dataKey="sales" 
                name="sales"
                radius={[4, 4, 0, 0]} 
                maxBarSize={60}
                className="fill-primary hover:fill-primary/80 transition-colors"
              />
            </RechartsBarChart>
          </ChartContainer>
        </div>
      </CardContent>
    </Card>
  );
};

export default SalesChart;
