import { useState } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import DashboardLayout from '@/components/dashboard/DashboardLayout';

// Define interfaces for type safety
interface PintaDataItem {
    month: string;
    year: number;
    price: number;
    volume: number;
}

interface EnhancedPintaDataItem extends PintaDataItem {
    index: string;
    label: string;
    pricePerML: string;
}

interface ChartConfigItem {
    title: string;
    yAxisLabel: string;
    color: string;
}

type DataTypeOption = 'index' | 'price' | 'volume';

type ChartConfig = {
    [key in DataTypeOption]: ChartConfigItem;
};

// Sample data for the Pint of Beer (Pinta) Index
const pintaData: PintaDataItem[] = [
    { month: 'Ene', year: 2020, price: 3.80, volume: 568 }, // Standard pint (568ml)
    { month: 'Feb', year: 2020, price: 3.80, volume: 568 },
    { month: 'Mar', year: 2020, price: 3.85, volume: 568 },
    { month: 'Abr', year: 2020, price: 3.85, volume: 568 },
    { month: 'May', year: 2020, price: 3.90, volume: 568 },
    { month: 'Jun', year: 2020, price: 3.90, volume: 568 },
    { month: 'Jul', year: 2020, price: 3.95, volume: 560 }, // Slight reduction
    { month: 'Ago', year: 2020, price: 3.95, volume: 560 },
    { month: 'Sep', year: 2020, price: 4.00, volume: 560 },
    { month: 'Oct', year: 2020, price: 4.00, volume: 560 },
    { month: 'Nov', year: 2020, price: 4.05, volume: 560 },
    { month: 'Dic', year: 2020, price: 4.10, volume: 560 },
    { month: 'Ene', year: 2021, price: 4.15, volume: 550 }, // Further reduction
    { month: 'Feb', year: 2021, price: 4.20, volume: 550 },
    { month: 'Mar', year: 2021, price: 4.25, volume: 550 },
    { month: 'Abr', year: 2021, price: 4.30, volume: 550 },
    { month: 'May', year: 2021, price: 4.35, volume: 550 },
    { month: 'Jun', year: 2021, price: 4.40, volume: 540 }, // Another reduction
    { month: 'Jul', year: 2021, price: 4.45, volume: 540 },
    { month: 'Ago', year: 2021, price: 4.50, volume: 540 },
    { month: 'Sep', year: 2021, price: 4.55, volume: 540 },
    { month: 'Oct', year: 2021, price: 4.60, volume: 540 },
    { month: 'Nov', year: 2021, price: 4.70, volume: 530 }, // Holiday season price jump
    { month: 'Dic', year: 2021, price: 4.80, volume: 530 },
    { month: 'Ene', year: 2022, price: 4.85, volume: 530 },
    { month: 'Feb', year: 2022, price: 4.90, volume: 530 },
    { month: 'Mar', year: 2022, price: 5.00, volume: 520 }, // Inflation impact
    { month: 'Abr', year: 2022, price: 5.10, volume: 520 },
    { month: 'May', year: 2022, price: 5.15, volume: 520 },
];

// Calculate the real value index (price per ml)
const dataWithIndex: EnhancedPintaDataItem[] = pintaData.map(item => ({
    ...item,
    index: ((item.price / item.volume) * 500).toFixed(2), // Price per 500ml for easier comparison
    label: `${item.month} ${item.year}`,
    pricePerML: (item.price / item.volume).toFixed(3)
}));

const Pinta: React.FC = () => {
    const [dataType, setDataType] = useState<DataTypeOption>('index');

    // Chart title and y-axis label based on selected data type
    const chartConfig: ChartConfig = {
        index: {
            title: 'Índice Pinta (Precio por 500ml)',
            yAxisLabel: 'Precio (€/500ml)',
            color: '#8884d8'
        },
        price: {
            title: 'Precio por Pinta de Cerveza',
            yAxisLabel: 'Precio (€)',
            color: '#82ca9d'
        },
        volume: {
            title: 'Volumen de la Pinta',
            yAxisLabel: 'Volumen (ml)',
            color: '#ffc658'
        }
    };

    return (
        <DashboardLayout>
            <div className="flex flex-col items-center w-full p-4 bg-white rounded-lg">
                <h2 className="text-xl font-bold mb-4">{chartConfig[dataType].title}</h2>

                {/* Data type selector */}
                <div className="flex mb-4 space-x-2">
                    <button
                        className={`px-3 py-1 rounded ${dataType === 'index' ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}
                        onClick={() => setDataType('index')}
                    >
                        Índice Pinta
                    </button>
                    <button
                        className={`px-3 py-1 rounded ${dataType === 'price' ? 'bg-green-600 text-white' : 'bg-gray-200'}`}
                        onClick={() => setDataType('price')}
                    >
                        Precio
                    </button>
                    <button
                        className={`px-3 py-1 rounded ${dataType === 'volume' ? 'bg-yellow-600 text-white' : 'bg-gray-200'}`}
                        onClick={() => setDataType('volume')}
                    >
                        Volumen
                    </button>
                </div>

                {/* Chart */}
                <div className="w-full h-64">
                    <ResponsiveContainer width="100%" height="100%">
                        <LineChart
                            data={dataWithIndex}
                            margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                        >
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis
                                dataKey="label"
                                tick={{ fontSize: 12 }}
                                interval={3}
                            />
                            <YAxis
                                label={{
                                    value: chartConfig[dataType].yAxisLabel,
                                    angle: -90,
                                    position: 'insideLeft',
                                    style: { textAnchor: 'middle' }
                                }}
                                domain={dataType === 'volume' ? [500, 580] : ['auto', 'auto']}
                            />
                            <Tooltip
                                formatter={(value: any) => [
                                    `${value} ${dataType === 'price' ? '€' : dataType === 'volume' ? 'ml' : '€/500ml'}`
                                ]}
                                labelFormatter={(label: string) => `${label}`}
                            />
                            <Legend />
                            <Line
                                type="monotone"
                                dataKey={dataType}
                                name={chartConfig[dataType].title}
                                stroke={chartConfig[dataType].color}
                                activeDot={{ r: 8 }}
                                strokeWidth={2}
                            />
                        </LineChart>
                    </ResponsiveContainer>
                </div>

                <div className="mt-4 text-sm text-gray-600">
                    <p>El Índice Pinta muestra la relación entre el precio y el volumen de cerveza a lo largo del tiempo.</p>
                    <p>Un valor más alto del índice indica menos valor por dinero (precio más alto o menor volumen).</p>
                    <p>Una pinta estándar debería tener 568ml, pero el volumen ha ido disminuyendo con el tiempo.</p>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default Pinta;
