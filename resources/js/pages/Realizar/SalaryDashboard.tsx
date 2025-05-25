import React, { useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';

// Define TypeScript interfaces
interface SalaryData {
  categoria: string;
  periodo: string;
  salario: number;
}

interface PeriodData {
  name: string;
  [key: string]: string | number;
}

interface CategoryDistData {
  name: string;
  value: number;
}

const SalaryDashboard: React.FC = () => {
  // State for filters with type definitions
  const [selectedPeriod, setSelectedPeriod] = useState<string>('todos');
  const [selectedCategories, setSelectedCategories] = useState<string[]>(['A', 'B', 'C']);

  // Sample data with type definition
  const salaryData: SalaryData[] = [
    { categoria: 'A', periodo: 'Q1', salario: 15000 },
    { categoria: 'A', periodo: 'Q2', salario: 16000 },
    { categoria: 'A', periodo: 'Q3', salario: 15500 },
    { categoria: 'A', periodo: 'Q4', salario: 17000 },
    { categoria: 'B', periodo: 'Q1', salario: 12000 },
    { categoria: 'B', periodo: 'Q2', salario: 12800 },
    { categoria: 'B', periodo: 'Q3', salario: 13200 },
    { categoria: 'B', periodo: 'Q4', salario: 13800 },
    { categoria: 'C', periodo: 'Q1', salario: 8500 },
    { categoria: 'C', periodo: 'Q2', salario: 8800 },
    { categoria: 'C', periodo: 'Q3', salario: 9100 },
    { categoria: 'C', periodo: 'Q4', salario: 9400 },
  ];

  // Filter data according to selections
  const filteredData: SalaryData[] = salaryData.filter(item => {
    const categoryMatch = selectedCategories.includes(item.categoria);
    const periodMatch = selectedPeriod === 'todos' || item.periodo === selectedPeriod;
    return categoryMatch && periodMatch;
  });

  // Data for period chart
  const periodData: PeriodData[] = selectedPeriod === 'todos'
    ? ['Q1', 'Q2', 'Q3', 'Q4'].map(period => {
      return {
        name: period,
        ...selectedCategories.reduce<Record<string, number>>((acc, cat) => {
          const value = salaryData.find(d => d.categoria === cat && d.periodo === period)?.salario || 0;
          acc[cat] = value;
          return acc;
        }, {})
      };
    })
    : [{
      name: selectedPeriod,
      ...selectedCategories.reduce<Record<string, number>>((acc, cat) => {
        const value = salaryData.find(d => d.categoria === cat && d.periodo === selectedPeriod)?.salario || 0;
        acc[cat] = value;
        return acc;
      }, {})
    }];

  // Data for category distribution chart
  const categoryDistData: CategoryDistData[] = selectedCategories.map(cat => {
    const total = filteredData
      .filter(item => item.categoria === cat)
      .reduce((sum, item) => sum + item.salario, 0);
    return { name: `Categoría ${cat}`, value: total };
  });

  // Colors for pie chart
  const COLORS: string[] = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8'];

  // Handle period change
  const handlePeriodChange = (e: React.ChangeEvent<HTMLSelectElement>): void => {
    setSelectedPeriod(e.target.value);
  };

  // Handle category change
  const handleCategoryChange = (cat: string): void => {
    if (selectedCategories.includes(cat)) {
      if (selectedCategories.length > 1) {
        setSelectedCategories(selectedCategories.filter(c => c !== cat));
      }
    } else {
      setSelectedCategories([...selectedCategories, cat]);
    }
  };

  // Calculate totals
  const totalSalary: number = filteredData.reduce((sum, item) => sum + item.salario, 0);

  return (
    <div className="p-6 rounded-lg shadow-lg mt-[20px]">
      <h1 className="text-2xl font-bold text-center mb-6 text-black">Panel de Control de Salarios</h1>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div className="p-4 rounded-lg shadow">
          <h2 className="text-lg font-semibold mb-2 text-black">Salarios Total</h2>
          <p className="text-3xl font-bold text-blue-600">{totalSalary.toLocaleString('es-ES')} $</p>
        </div>

        <div className="p-4 rounded-lg shadow">
          <h2 className="text-lg font-semibold mb-2 text-black">Filtro por Periodo</h2>
          <select
            className="w-full p-2 border rounded"
            value={selectedPeriod}
            onChange={handlePeriodChange}
          >
            <option value="todos">Todos los periodos</option>
            <option value="Q1">Q1</option>
            <option value="Q2">Q2</option>
            <option value="Q3">Q3</option>
            <option value="Q4">Q4</option>
          </select>
        </div>

        <div className="p-4 rounded-lg shadow">
          <h2 className="text-lg font-semibold mb-2">Filtro por Categorías</h2>
          <div className="flex flex-wrap gap-2">
            {['A', 'B', 'C'].map((cat) => (
              <button
                key={cat}
                className={`px-3 py-1 rounded ${selectedCategories.includes(cat)
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 text-gray-700'
                  }`}
                onClick={() => handleCategoryChange(cat)}
              >
                Categoría {cat}
              </button>
            ))}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div className="p-4 rounded-lg shadow">
          <h2 className="text-lg font-semibold mb-4">Salarios por Periodo y Categoría</h2>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart
              data={periodData}
              className='[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700'
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Legend />
              {selectedCategories.includes('A') && <Bar dataKey="A" name="Categoría A" fill="#0088FE" />}
              {selectedCategories.includes('B') && <Bar dataKey="B" name="Categoría B" fill="#00C49F" />}
              {selectedCategories.includes('C') && <Bar dataKey="C" name="Categoría C" fill="#FFBB28" />}
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="p-4 rounded-lg shadow">
          <h2 className="text-lg font-semibold mb-4">Distribución por Categoría</h2>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={categoryDistData}
                cx="50%"
                cy="50%"
                labelLine={true}
                outerRadius={100}
                fill="#8884d8"
                dataKey="value"
                label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
              >
                {categoryDistData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Pie>
              <Tooltip formatter={(value) => value.toLocaleString('es-ES') + ' €'} />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      <div className="p-4 rounded-lg shadow">
        <h2 className="text-lg font-semibold mb-4">Tabla de Salarios</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full">
            <thead className="">
              <tr>
                <th className="py-2 px-4 border-b">Categoría</th>
                <th className="py-2 px-4 border-b">Periodo</th>
                <th className="py-2 px-4 border-b">Salario</th>
              </tr>
            </thead>
            <tbody>
              {filteredData.map((row, index) => (
                <tr key={index}>
                  <td className="py-2 px-4 border-b">Categoría {row.categoria}</td>
                  <td className="py-2 px-4 border-b">{row.periodo}</td>
                  <td className="py-2 px-4 border-b text-right">{row.salario.toLocaleString('es-ES')} €</td>
                </tr>
              ))}
            </tbody>
            <tfoot className="font-semibold">
              <tr>
                <td className="py-2 px-4 border-b" colSpan={2}>Total</td>
                <td className="py-2 px-4 border-b text-right">{totalSalary.toLocaleString('es-ES')} €</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  );
};

export default SalaryDashboard;
