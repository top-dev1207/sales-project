import React, { useState } from 'react';
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ComposedChart
} from 'recharts';

// Sample data - sales and weather by month
const data = [
  { month: 'Jan', sales: 4000, temperature: 5, precipitation: 65 },
  { month: 'Feb', sales: 3000, temperature: 7, precipitation: 60 },
  { month: 'Mar', sales: 5000, temperature: 12, precipitation: 55 },
  { month: 'Apr', sales: 7000, temperature: 16, precipitation: 50 },
  { month: 'May', sales: 6500, temperature: 20, precipitation: 40 },
  { month: 'Jun', sales: 8500, temperature: 25, precipitation: 30 },
  { month: 'Jul', sales: 10000, temperature: 28, precipitation: 20 },
  { month: 'Aug', sales: 11000, temperature: 27, precipitation: 25 },
  { month: 'Sep', sales: 9000, temperature: 22, precipitation: 35 },
  { month: 'Oct', sales: 8000, temperature: 17, precipitation: 45 },
  { month: 'Nov', sales: 6000, temperature: 10, precipitation: 55 },
  { month: 'Dec', sales: 7500, temperature: 6, precipitation: 62 }
];

// Sample data - sales and weather by week
const weeklyData = [
  { week: 'W1', sales: 1200, temperature: 18, precipitation: 25 },
  { week: 'W2', sales: 1400, temperature: 22, precipitation: 10 },
  { week: 'W3', sales: 1100, temperature: 20, precipitation: 40 },
  { week: 'W4', sales: 1600, temperature: 24, precipitation: 5 },
  { week: 'W5', sales: 1800, temperature: 26, precipitation: 2 },
  { week: 'W6', sales: 1500, temperature: 23, precipitation: 15 },
  { week: 'W7', sales: 1300, temperature: 19, precipitation: 35 },
  { week: 'W8', sales: 1700, temperature: 21, precipitation: 20 }
];

// Sample data - sales and weather by day
const dailyData = [
  { day: 'Mon', sales: 320, temperature: 22, precipitation: 0 },
  { day: 'Tue', sales: 350, temperature: 24, precipitation: 5 },
  { day: 'Wed', sales: 370, temperature: 20, precipitation: 40 },
  { day: 'Thu', sales: 420, temperature: 18, precipitation: 30 },
  { day: 'Fri', sales: 550, temperature: 21, precipitation: 10 },
  { day: 'Sat', sales: 600, temperature: 23, precipitation: 0 },
  { day: 'Sun', sales: 450, temperature: 25, precipitation: 0 }
];

const SalesWeatherChart = () => {
  const [timeFrame, setTimeFrame] = useState('monthly');
  const [chartType, setChartType] = useState('combo');

  // Select data based on timeframe
  const selectedData = timeFrame === 'monthly'
    ? data
    : timeFrame === 'weekly'
      ? weeklyData
      : dailyData;

  // Get the key for the x-axis based on timeframe
  const xAxisKey = timeFrame === 'monthly'
    ? 'month'
    : timeFrame === 'weekly'
      ? 'week'
      : 'day';

  return (
    <div className="flex flex-col w-full h-full bg-gray-50 p-4 rounded-lg shadow mt-[20px]">
      <div className="mb-4 flex justify-between items-center">
        <h2 className="text-xl font-bold text-gray-800">Sales and Weather Relationship</h2>
        <div className="flex space-x-4">
          <div className="flex items-center space-x-2">
            <label htmlFor="timeframe" className="text-sm font-medium text-gray-700">Time Period:</label>
            <select
              id="timeframe"
              value={timeFrame}
              onChange={(e) => setTimeFrame(e.target.value)}
              className="border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div className="flex items-center space-x-2">
            <label htmlFor="charttype" className="text-sm font-medium text-gray-700">Chart Type:</label>
            <select
              id="charttype"
              value={chartType}
              onChange={(e) => setChartType(e.target.value)}
              className="border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm"
            >
              <option value="combo">Combined</option>
              <option value="bar">Bar Chart</option>
              <option value="line">Line Chart</option>
            </select>
          </div>
        </div>
      </div>

      <div className="flex-1 w-full">
        <ResponsiveContainer width="100%" height={400}>
          {chartType === 'combo' ? (
            <ComposedChart data={selectedData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey={xAxisKey} />
              <YAxis yAxisId="left" orientation="left" label={{ value: 'Sales ($)', angle: -90, position: 'insideLeft' }} />
              <YAxis yAxisId="right" orientation="right" label={{ value: 'Temperature (°C)', angle: 90, position: 'insideRight' }} />
              <YAxis yAxisId="far-right" orientation="right" axisLine={false} tickLine={false} tick={false} />
              <Tooltip />
              <Legend />
              <Bar yAxisId="left" dataKey="sales" name="Sales" fill="#3f83f8" barSize={30} />
              <Line yAxisId="right" type="monotone" dataKey="temperature" name="Temperature" stroke="#ff7300" strokeWidth={3} />
              <Line yAxisId="far-right" type="monotone" dataKey="precipitation" name="Precipitation (%)" stroke="#8884d8" strokeWidth={3} />
            </ComposedChart>
          ) : chartType === 'bar' ? (
            <BarChart data={selectedData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey={xAxisKey} />
              <YAxis />
              <Tooltip />
              <Legend />
              <Bar dataKey="sales" name="Sales" fill="#3f83f8" />
              <Bar dataKey="temperature" name="Temperature (°C)" fill="#ff7300" />
              <Bar dataKey="precipitation" name="Precipitation (%)" fill="#8884d8" />
            </BarChart>
          ) : (
            <LineChart data={selectedData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey={xAxisKey} />
              <YAxis yAxisId="left" orientation="left" />
              <YAxis yAxisId="right" orientation="right" />
              <Tooltip />
              <Legend />
              <Line yAxisId="left" type="monotone" dataKey="sales" name="Sales" stroke="#3f83f8" strokeWidth={3} />
              <Line yAxisId="right" type="monotone" dataKey="temperature" name="Temperature (°C)" stroke="#ff7300" strokeWidth={3} />
              <Line yAxisId="right" type="monotone" dataKey="precipitation" name="Precipitation (%)" stroke="#8884d8" strokeWidth={3} />
            </LineChart>
          )}
        </ResponsiveContainer>
      </div>

      {/* <div className="mt-4 p-3 rounded-md border border-gray-200">
        <h3 className="text-lg font-semibold text-gray-700 mb-2">Analysis</h3>
        <p className="text-gray-600">
          {timeFrame === 'monthly'
            ? 'Sales show a strong correlation with temperature, peaking during summer months when temperatures are highest. Precipitation appears to have an inverse relationship with sales.'
            : timeFrame === 'weekly'
              ? 'Weekly sales data indicates higher revenues during weeks with higher temperatures and lower precipitation.'
              : 'Daily sales pattern shows weekend peaks (especially Saturday), with better performance on days with no precipitation.'}
        </p>
      </div> */}
    </div>
  );
};

export default SalesWeatherChart;
