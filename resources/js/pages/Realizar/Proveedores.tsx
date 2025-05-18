import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Line, Bar } from 'react-chartjs-2';
import moment from 'moment';
import 'moment/locale/es';

moment.locale('es');

// TypeScript interfaces for API responses
interface Proveedor {
  nro_proveedor: number;
  nombre: string;
  cuit?: string;
  contacto?: string;
  telefono?: string;
  email?: string;
  direccion?: string;
}

interface FacturaPendiente {
  id: number;
  nro_documento: number;
  nro_factura: string;
  fecha_factura: string | null;
  fecha_limite: string | null;
  total: number;
  saldo: number;
  pagado: boolean;
  estadoDocumento: number;
}

interface HistoricoPago {
  anio: number;
  mes: number;
  monto_total: number;
  monto_pagado: number;
}

interface DeudaTotal {
  proveedor: number;
  nombre: string;
  deuda_total: number;
  cantidad_facturas: number;
}

interface PasivoFacturas {
  total_pasivo: number;
  total_pagado: number;
  total_pendiente: number;
  cantidad_facturas: number;
}

interface HistoricoItem {
  periodo: string;
  fecha_inicio_periodo: string;
  fecha_fin_periodo: string;
  proveedor: number;
  nombre: string;
  monto_total: number;
  monto_pagado: number;
  monto_pendiente: number;
}

interface HistoricoDataset {
  [proveedorId: string]: {
    nombre: string;
    total: { [periodo: string]: number };
    pagado: { [periodo: string]: number };
    pendiente: { [periodo: string]: number };
  };
}

interface HistoricoData {
  periodos: string[];
  proveedores: { [id: string]: string };
  dataset: HistoricoDataset;
  raw: HistoricoItem[];
}

interface DetalleProveedor {
  proveedor: Proveedor;
  facturas_pendientes: FacturaPendiente[];
  historico_pagos: HistoricoPago[];
}

interface HistoricoOptions {
  periodo: 'semanal' | 'mensual';
  proveedorId: number | null;
  fechaInicio: string | null;
  fechaFin: string | null;
}

interface ChartDataset {
  label: string;
  data: number[];
  borderColor: string;
  backgroundColor: string;
  fill: boolean;
  tension: number;
}

interface ChartData {
  labels: string[];
  datasets: ChartDataset[];
}

// Type definitions for table summary
type TableDataRow = DeudaTotal | FacturaPendiente;
type ReducerFn = (prev: number, curr: TableDataRow) => number;

const API_URL = '/api/proveedores';

const Dashboard: React.FC = () => {
  // States
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [deudaTotal, setDeudaTotal] = useState<DeudaTotal[]>([]);
  const [pasivoFacturas, setPasivoFacturas] = useState<PasivoFacturas | null>(null);
  const [historicoData, setHistoricoData] = useState<HistoricoData | null>(null);
  const [historicoOptions, setHistoricoOptions] = useState<HistoricoOptions>({
    periodo: 'mensual',
    proveedorId: null,
    fechaInicio: null,
    fechaFin: null
  });
  const [proveedorSeleccionado, setProveedorSeleccionado] = useState<number | null>(null);
  const [detalleProveedor, setDetalleProveedor] = useState<DetalleProveedor | null>(null);
  const [activeTab, setActiveTab] = useState<string>("1");

  // API service methods
  const proveedoresService = {
    getDeudaTotal: () => {
      return axios.get<{ status: string; data: DeudaTotal[] }>(`${API_URL}/deuda-total`);
    },
    getPasivoFacturas: () => {
      return axios.get<{ status: string; data: PasivoFacturas }>(`${API_URL}/pasivo-facturas`);
    },
    getHistoricoPagos: (
      periodo: 'semanal' | 'mensual' = 'mensual',
      proveedorId: number | null = null,
      fechaInicio: string | null = null,
      fechaFin: string | null = null
    ) => {
      interface HistoricoParams {
        periodo: 'semanal' | 'mensual';
        proveedor_id?: number;
        fecha_inicio?: string;
        fecha_fin?: string;
      }
      
      let params: HistoricoParams = { periodo };
      
      if (proveedorId) params.proveedor_id = proveedorId;
      if (fechaInicio) params.fecha_inicio = fechaInicio;
      if (fechaFin) params.fecha_fin = fechaFin;
      
      return axios.get<{ status: string; data: HistoricoData }>(`${API_URL}/historico-pagos`, { params });
    },
    getDetalleProveedor: (proveedorId: number) => {
      return axios.get<{ status: string; data: DetalleProveedor }>(`${API_URL}/detalle/${proveedorId}`);
    }
  };

  // Load initial data
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);

        // Fetch all data in parallel
        const [deudaRes, pasivoRes, historicoRes] = await Promise.all([
          proveedoresService.getDeudaTotal(),
          proveedoresService.getPasivoFacturas(),
          proveedoresService.getHistoricoPagos(historicoOptions.periodo)
        ]);

        setDeudaTotal(deudaRes.data.data);
        setPasivoFacturas(pasivoRes.data.data);
        setHistoricoData(historicoRes.data.data);
      } catch (err) {
        console.error('Error loading dashboard data:', err);
        setError('Error al cargar los datos. Por favor, intente nuevamente.');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  // Load historical data when options change
  useEffect(() => {
    const fetchHistorico = async () => {
      try {
        setLoading(true);
        const response = await proveedoresService.getHistoricoPagos(
          historicoOptions.periodo,
          historicoOptions.proveedorId,
          historicoOptions.fechaInicio,
          historicoOptions.fechaFin
        );
        setHistoricoData(response.data.data);
      } catch (err) {
        console.error('Error loading historical data:', err);
        setError('Error al cargar los datos históricos. Por favor, intente nuevamente.');
      } finally {
        setLoading(false);
      }
    };

    // Only fetch if component is already mounted and initial data is loaded
    if (!loading && historicoData) {
      fetchHistorico();
    }
  }, [historicoOptions]);

  // Load provider details when selected
  useEffect(() => {
    if (!proveedorSeleccionado) return;

    const fetchDetalleProveedor = async () => {
      try {
        setLoading(true);
        const response = await proveedoresService.getDetalleProveedor(proveedorSeleccionado);
        setDetalleProveedor(response.data.data);
      } catch (err) {
        console.error('Error loading provider details:', err);
        setError('Error al cargar los detalles del proveedor. Por favor, intente nuevamente.');
      } finally {
        setLoading(false);
      }
    };

    fetchDetalleProveedor();
  }, [proveedorSeleccionado]);

  // Handle period change
  const handlePeriodChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    setHistoricoOptions({
      ...historicoOptions,
      periodo: event.target.value as 'semanal' | 'mensual'
    });
  };

  // Handle provider filter change
  const handleProveedorChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    setHistoricoOptions({
      ...historicoOptions,
      proveedorId: event.target.value ? Number(event.target.value) : null
    });
  };

  // Handle date range change
  const handleDateChange = (event: React.ChangeEvent<HTMLInputElement>, type: 'inicio' | 'fin') => {
    setHistoricoOptions({
      ...historicoOptions,
      [type === 'inicio' ? 'fechaInicio' : 'fechaFin']: event.target.value
    });
  };

  // Handle provider selection for details
  const handleProveedorSelect = (proveedorId: number) => {
    setProveedorSeleccionado(proveedorId);
  };

  // Format chart data for historical data
  const formatChartData = (): ChartData | null => {
    if (!historicoData || !historicoData.periodos || !historicoData.dataset) return null;

    const { periodos, dataset } = historicoData;
    const colors = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];

    // Prepare line chart data
    return {
      labels: periodos,
      datasets: Object.keys(dataset).map((proveedorId: string, index: number) => {
        const proveedor = dataset[proveedorId];
        const color = colors[index % colors.length];
        
        return {
          label: proveedor.nombre,
          data: periodos.map((periodo: string) => proveedor.pendiente[periodo] || 0),
          borderColor: color,
          backgroundColor: color + '20',
          fill: false,
          tension: 0.1
        };
      })
    };
  };

  // Calculate table summary totals
  const calcularTotal = <T extends TableDataRow>(data: T[], campo: keyof T): number => {
    return data.reduce((prev: number, curr: T) => {
      const valor = curr[campo];
      return prev + (typeof valor === 'number' ? valor : parseFloat(String(valor)) || 0);
    }, 0);
  };

  if (loading && !deudaTotal.length) {
    return (
      <div className="flex justify-center items-center p-10 h-screen">
        <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4" role="alert">
        <strong className="font-bold">Error!</strong>
        <span className="block sm:inline ml-2">{error}</span>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-6">
      <h1 className="text-2xl font-bold mb-6 text-gray-800">Dashboard de Deuda a Proveedores</h1>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {/* Deuda total de proveedores section */}
        <div className="bg-white rounded-lg shadow-md p-4">
          <h2 className="text-xl font-semibold mb-2 text-gray-700">Deuda Total de Proveedores a la Fecha</h2>
          <p className="text-sm text-gray-600 mb-4">Saldo pendiente de pago de facturas, agrupado por proveedor</p>
          
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Facturas Pendientes</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deuda Total ($)</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {deudaTotal.map(item => (
                  <tr key={item.proveedor} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <a 
                        className="text-blue-600 hover:text-blue-800 font-medium cursor-pointer"
                        onClick={() => handleProveedorSelect(item.proveedor)}
                      >
                        {item.nombre}
                      </a>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                      {item.cantidad_facturas}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-medium">
                      {item.deuda_total.toLocaleString('es-AR', { minimumFractionDigits: 2 })}
                    </td>
                  </tr>
                ))}
              </tbody>
              <tfoot className="bg-gray-50">
                <tr>
                  <td className="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">TOTAL</td>
                  <td className="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                    {calcularTotal(deudaTotal, 'cantidad_facturas')}
                  </td>
                  <td className="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                    {calcularTotal(deudaTotal, 'deuda_total').toLocaleString('es-AR', { minimumFractionDigits: 2 })}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        {/* Pasivo de facturas actual section */}
        <div className="bg-white rounded-lg shadow-md p-4">
          <h2 className="text-xl font-semibold mb-2 text-gray-700">Pasivo de Facturas Actual</h2>
          <p className="text-sm text-gray-600 mb-4">Total de $ de Facturas cargadas (validadas y no)</p>
          
          <div className="grid grid-cols-3 gap-4">
            <div className="bg-gray-50 rounded-lg p-4 text-center">
              <p className="text-sm text-gray-600">Total Pasivo</p>
              <h3 className="text-xl font-bold text-gray-800 mb-1">
                ${(pasivoFacturas?.total_pasivo || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 })}
              </h3>
              <p className="text-xs text-gray-500">{pasivoFacturas?.cantidad_facturas || 0} facturas</p>
            </div>
            
            <div className="bg-blue-50 rounded-lg p-4 text-center">
              <p className="text-sm text-blue-600">Pagado</p>
              <h3 className="text-xl font-bold text-blue-800 mb-1">
                ${(pasivoFacturas?.total_pagado || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 })}
              </h3>
              <p className="text-xs text-blue-600">
                {pasivoFacturas && pasivoFacturas.total_pasivo > 0 
                  ? ((pasivoFacturas.total_pagado / pasivoFacturas.total_pasivo) * 100).toFixed(1) 
                  : "0.0"}%
              </p>
            </div>
            
            <div className="bg-red-50 rounded-lg p-4 text-center">
              <p className="text-sm text-red-600">Pendiente</p>
              <h3 className="text-xl font-bold text-red-800 mb-1">
                ${(pasivoFacturas?.total_pendiente || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 })}
              </h3>
              <p className="text-xs text-red-600">
                {pasivoFacturas && pasivoFacturas.total_pasivo > 0 
                  ? ((pasivoFacturas.total_pendiente / pasivoFacturas.total_pasivo) * 100).toFixed(1) 
                  : "0.0"}%
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Históricos section */}
      <div className="bg-white rounded-lg shadow-md p-4 mb-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-semibold text-gray-700">Históricos de Deuda y Pagos a Proveedores</h2>
          
          <div className="flex gap-4">
            <select 
              className="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={historicoOptions.periodo}
              onChange={handlePeriodChange}
            >
              <option value="mensual">Mensual</option>
              <option value="semanal">Semanal</option>
            </select>
            
            <select
              className="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={historicoOptions.proveedorId || ""}
              onChange={handleProveedorChange}
            >
              <option value="">Todos los proveedores</option>
              {deudaTotal.map(proveedor => (
                <option key={proveedor.proveedor} value={proveedor.proveedor}>
                  {proveedor.nombre}
                </option>
              ))}
            </select>
            
            <div className="flex gap-2">
              <input
                type="date"
                className="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={historicoOptions.fechaInicio || ""}
                onChange={(e) => handleDateChange(e, 'inicio')}
                placeholder="Fecha inicio"
              />
              <input
                type="date"
                className="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={historicoOptions.fechaFin || ""}
                onChange={(e) => handleDateChange(e, 'fin')}
                placeholder="Fecha fin"
              />
            </div>
          </div>
        </div>
        
        <p className="text-sm text-gray-600 mb-6">
          Históricos Semanales y Mensuales de deuda y pagos, a lo largo del tiempo, para ver cómo se le va pagando a cada proveedor
        </p>
        
        <div className={loading ? "opacity-50" : ""}>
          {loading && (
            <div className="absolute inset-0 flex items-center justify-center">
              <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
            </div>
          )}
          
          {historicoData && formatChartData() && (
            <div className="h-96">
              <Line
                data={formatChartData()!}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                    y: {
                      beginAtZero: true,
                      title: {
                        display: true,
                        text: 'Monto Pendiente ($)'
                      }
                    }
                  }
                }}
              />
            </div>
          )}
        </div>
      </div>

      {/* Detalle de proveedor section */}
      {detalleProveedor && (
        <div className="bg-white rounded-lg shadow-md p-4 relative">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-semibold text-gray-700">
              Detalle del Proveedor: {detalleProveedor.proveedor.nombre}
            </h2>
            
            <button 
              className="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded"
              onClick={() => setProveedorSeleccionado(null)}
            >
              Cerrar
            </button>
          </div>
          
          <div className="mb-4">
            <div className="border-b border-gray-200">
              <nav className="flex -mb-px">
                <button
                  className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${
                    activeTab === "1"
                      ? "border-blue-500 text-blue-600"
                      : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                  }`}
                  onClick={() => setActiveTab("1")}
                >
                  Información General
                </button>
                <button
                  className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${
                    activeTab === "2"
                      ? "border-blue-500 text-blue-600"
                      : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                  }`}
                  onClick={() => setActiveTab("2")}
                >
                  Facturas Pendientes
                </button>
                <button
                  className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${
                    activeTab === "3"
                      ? "border-blue-500 text-blue-600"
                      : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                  }`}
                  onClick={() => setActiveTab("3")}
                >
                  Histórico de Pagos
                </button>
              </nav>
            </div>
            
            <div className="py-4">
              {activeTab === "1" && (
                <div className="grid grid-cols-3 gap-6">
                  <div>
                    <p className="mb-2"><span className="font-semibold">CUIT:</span> {detalleProveedor.proveedor.cuit}</p>
                    <p className="mb-2"><span className="font-semibold">Contacto:</span> {detalleProveedor.proveedor.contacto}</p>
                  </div>
                  <div>
                    <p className="mb-2"><span className="font-semibold">Teléfono:</span> {detalleProveedor.proveedor.telefono}</p>
                    <p className="mb-2"><span className="font-semibold">Email:</span> {detalleProveedor.proveedor.email}</p>
                  </div>
                  <div>
                    <p className="mb-2"><span className="font-semibold">Dirección:</span> {detalleProveedor.proveedor.direccion}</p>
                  </div>
                </div>
              )}
              
              {activeTab === "2" && (
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nº Factura</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total ($)</th>
                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pendiente ($)</th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {detalleProveedor.facturas_pendientes.map(factura => (
                        <tr key={factura.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{factura.nro_factura}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {factura.fecha_factura ? moment(factura.fecha_factura).format('DD/MM/YYYY') : '-'}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {factura.fecha_limite ? moment(factura.fecha_limite).format('DD/MM/YYYY') : '-'}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                            {factura.total.toLocaleString('es-AR', { minimumFractionDigits: 2 })}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                            {factura.saldo.toLocaleString('es-AR', { minimumFractionDigits: 2 })}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                    <tfoot className="bg-gray-50">
                      <tr>
                        <td colSpan={3} className="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">TOTAL</td>
                        <td className="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                          {calcularTotal(detalleProveedor.facturas_pendientes, 'total').toLocaleString('es-AR', { minimumFractionDigits: 2 })}
                        </td>
                        <td className="px-6 py-3 text-right text-xs font-bold text-red-700 uppercase tracking-wider">
                          {calcularTotal(detalleProveedor.facturas_pendientes, 'saldo').toLocaleString('es-AR', { minimumFractionDigits: 2 })}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              )}
              
              {activeTab === "3" && (
                <div className="h-80">
                  <Bar
                    data={{
                      labels: detalleProveedor.historico_pagos.map(item => 
                        `${moment().month(item.mes - 1).format('MMM')} ${item.anio}`
                      ),
                      datasets: [
                        {
                          label: 'Total',
                          data: detalleProveedor.historico_pagos.map(item => item.monto_total),
                          backgroundColor: '#1f77b4'
                        },
                        {
                          label: 'Pagado',
                          data: detalleProveedor.historico_pagos.map(item => item.monto_pagado),
                          backgroundColor: '#2ca02c'
                        }
                      ]
                    }}
                    options={{
                      responsive: true,
                      maintainAspectRatio: false,
                      scales: {
                        y: {
                          beginAtZero: true,
                          title: {
                            display: true,
                            text: 'Monto ($)'
                          }
                        }
                      }
                    }}
                  />
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Dashboard;