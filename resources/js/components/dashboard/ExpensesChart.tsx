
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PieChart as PieChartIcon } from 'lucide-react';
import { PieChart as RechartsPieChart, Pie, Cell, ResponsiveContainer } from 'recharts';
import { useDashboard } from '@/context/DashboardContext';
import { ChartContainer, ChartTooltip, ChartTooltipContent, ChartLegend, ChartLegendContent } from '@/components/ui/chart';

// Mock data
const getDailyExpenses = () => [
  { name: 'Food', value: 1400, percentage: 35 },
  { name: 'Labor', value: 1200, percentage: 30 },
  { name: 'Utilities', value: 600, percentage: 15 },
  { name: 'Rent', value: 500, percentage: 12.5 },
  { name: 'Other', value: 300, percentage: 7.5 },
];

const getWeeklyExpenses = () => [
  { name: 'Food', value: 9800, percentage: 35 },
  { name: 'Labor', value: 8400, percentage: 30 },
  { name: 'Utilities', value: 4200, percentage: 15 },
  { name: 'Rent', value: 3500, percentage: 12.5 },
  { name: 'Other', value: 2100, percentage: 7.5 },
];

const getMonthlyExpenses = () => [
  { name: 'Food', value: 42000, percentage: 35 },
  { name: 'Labor', value: 36000, percentage: 30 },
  { name: 'Utilities', value: 18000, percentage: 15 },
  { name: 'Rent', value: 15000, percentage: 12.5 },
  { name: 'Other', value: 9000, percentage: 7.5 },
];

const COLORS = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];

// Chart configuration
const config = {
  Food: { label: "Food", color: "#3B82F6" },
  Labor: { label: "Labor", color: "#10B981" },
  Utilities: { label: "Utilities", color: "#F59E0B" },
  Rent: { label: "Rent", color: "#EF4444" },
  Other: { label: "Other", color: "#8B5CF6" }
};

const ExpensesChart = () => {
  const { dateRange } = useDashboard();
  
  const getData = () => {
    switch (dateRange) {
      case 'daily':
        return getDailyExpenses();
      case 'weekly':
        return getWeeklyExpenses();
      case 'monthly':
        return getMonthlyExpenses();
      default:
        return getDailyExpenses();
    }
  };

  const data = getData();
  
  return (
    <Card className="h-full">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-base font-medium">Expense Breakdown</CardTitle>
        <PieChartIcon className="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent className="pt-4">
        <div className="h-[350px] w-full">
          <ChartContainer config={config} className="w-full">
            <RechartsPieChart className="animate-fade-in">
              <Pie
                data={data}
                cx="50%"
                cy="50%"
                labelLine={false}
                outerRadius={100}
                innerRadius={70}
                dataKey="value"
              >
                {data.map((entry, index) => (
                  <Cell 
                    key={`cell-${index}`} 
                    fill={COLORS[index % COLORS.length]} 
                    className="transition-opacity hover:opacity-80"
                  />
                ))}
              </Pie>
              <ChartTooltip
                content={({ active, payload }) => {
                  if (!active || !payload?.length) return null;
                  const entry = payload[0];
                  return (
                    <ChartTooltipContent
                      className="bg-white/95 shadow-lg border border-border/40 backdrop-blur-sm"
                      formatter={(value, name) => {
                        const item = data.find(d => d.name === name);
                        return [
                          <>
                            <div className="font-medium">${value.toLocaleString()}</div>
                            <div className="text-muted-foreground text-xs">{item?.percentage}% of total</div>
                          </>,
                          name
                        ];
                      }}
                    />
                  );
                }}
              />
              <ChartLegend
                content={<ChartLegendContent className="flex flex-wrap justify-center gap-4 pt-6" />}
              />
            </RechartsPieChart>
          </ChartContainer>
        </div>
      </CardContent>
    </Card>
  );
};

export default ExpensesChart;
