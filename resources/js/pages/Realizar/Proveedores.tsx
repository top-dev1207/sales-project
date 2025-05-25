import React, { useState, useEffect } from 'react';
import { LineChart, AreaChart, BarChart, PieChart, XAxis, YAxis, CartesianGrid, Tooltip, Legend, Line, Area, Bar, Pie, Cell, ResponsiveContainer } from 'recharts';

// Interfaces para los datos de respuesta de la API
interface Proveedor {
  id: string;
  nombre: string;
  deuda: number;
  porcentaje: number;
  cuit?: string;
  dias_credito?: number;
}

interface RespuestaDeuda {
  success: boolean;
  proveedores: Proveedor[];
}

interface ConteoFacturas {
  facturas_pendientes_pago: number;
  facturas_validadas: number;
  facturas_por_validar: number;
  facturas_total: number;
}

interface TotalesMontos {
  total_pendiente_pago: number;
  total_validado: number;
  total_por_validar: number;
  total_general: number;
}

interface RespuestaPasivo {
  success: boolean;
  conteo: ConteoFacturas;
  totales: TotalesMontos;
}

interface PuntoDataHistorico {
  etiqueta: string;
  fecha_inicio?: string; // Fecha inicio del período
  fecha_fin?: string; // Fecha fin del período
  deuda_total: number;
  nuevas_facturas: number;
  pagos_realizados: number;
}

interface PuntoTendenciaPago {
  etiqueta: string;
  fecha_inicio?: string;
  fecha_fin?: string;
  nuevas_facturas_monto: number;
  pagos_realizados_monto: number;
  balance_mensual: number;
}

interface RespuestaHistorico {
  success: boolean;
  datos_mensuales: PuntoDataHistorico[];
  datos_semanales: PuntoDataHistorico[];
  tendencia_pagos: PuntoTendenciaPago[];
}

interface DeudaPorAntiguedad {
  current: number;
  '1_30': number;
  '31_60': number;
  '61_90': number;
  over_90: number;
}

interface FacturaPendiente {
  id: string;
  nro_factura?: string;
  nro_documento?: string;
  total: number;
  fecha_limite: string;
  dias_vencidos: number;
}

interface DetalleProveedor {
  success: boolean;
  proveedor: Proveedor;
  deuda_por_antiguedad: DeudaPorAntiguedad;
  facturas_pendientes_detalle: FacturaPendiente[];
  tiempo_promedio_pago: number;
}

const Proveedores: React.FC = () => {
  // Variables de estado para los datos de la API
  const [datosDeuda, setDatosDeuda] = useState<RespuestaDeuda | null>(null);
  const [datosPasivo, setDatosPasivo] = useState<RespuestaPasivo | null>(null);
  const [datosHistoricos, setDatosHistoricos] = useState<RespuestaHistorico | null>(null);
  const [proveedorSeleccionado, setProveedorSeleccionado] = useState<string | null>(null);
  const [detalleProveedor, setDetalleProveedor] = useState<DetalleProveedor | null>(null);
  const [cargando, setCargando] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  // Controles de visualización de gráficos
  const [periodoHistorico, setPeriodoHistorico] = useState<'weekly' | 'monthly'>('monthly');
  const [tipoGrafico, setTipoGrafico] = useState<'line' | 'area' | 'bar'>('line');

  // Colores para los gráficos
  const COLORES = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d'];

  // Formatear moneda
  const formatearMoneda = (valor: number): string => {
    return new Intl.NumberFormat('es-AR', {
      style: 'currency',
      currency: 'ARS',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(valor);
  };

  // Formatear etiquetas de fecha para datos semanales
  const formatearEtiquetaSemana = (item: PuntoDataHistorico): string => {
    if (item.fecha_inicio && item.fecha_fin) {
      const fechaInicio = new Date(item.fecha_inicio);
      const fechaFin = new Date(item.fecha_fin);
      return `${fechaInicio.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit' })} - ${fechaFin.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit' })}`;
    }
    return item.etiqueta;
  };

  // Cargar datos al montar el componente
  useEffect(() => {
    const cargarDatos = async (): Promise<void> => {
      setCargando(true);
      try {
        // Cargar datos de todos los endpoints
        const [respuestaDeuda, respuestaPasivo, respuestaHistorico] = await Promise.all([
          fetch('/api/proveedores/deuda-total'),
          fetch('/api/proveedores/pasivo-facturas'),
          fetch('/api/proveedores/historico-pagos')
        ]);

        if (!respuestaDeuda.ok || !respuestaPasivo.ok || !respuestaHistorico.ok) {
          throw new Error('Error al cargar los datos');
        }

        const datosDeuda: RespuestaDeuda = await respuestaDeuda.json();
        const datosPasivo: RespuestaPasivo = await respuestaPasivo.json();
        const datosHistoricos: RespuestaHistorico = await respuestaHistorico.json();

        // Añadir rango de fechas a datos semanales para mejor visualización
        datosHistoricos.datos_semanales = datosHistoricos.datos_semanales.map(semana => {
          // Si no hay fecha_inicio/fecha_fin en la respuesta de la API, podríamos derivarlas
          // Esto es solo un ejemplo - ajustar según los datos reales
          const etiquetaSemana = semana.etiqueta;
          const numeroSemana = parseInt(etiquetaSemana.replace('W', ''));

          // Esto es solo una aproximación - en realidad usarías fechas reales de la API
          const fechaInicio = new Date(2023, 0, 1 + (numeroSemana - 1) * 7);
          const fechaFin = new Date(fechaInicio);
          fechaFin.setDate(fechaInicio.getDate() + 6);

          return {
            ...semana,
            fecha_inicio: fechaInicio.toISOString().split('T')[0],
            fecha_fin: fechaFin.toISOString().split('T')[0]
          };
        });

        setDatosDeuda(datosDeuda);
        setDatosPasivo(datosPasivo);
        setDatosHistoricos(datosHistoricos);

        // Establecer el primer proveedor como seleccionado si está disponible
        if (datosDeuda.success && datosDeuda.proveedores.length > 0) {
          setProveedorSeleccionado(datosDeuda.proveedores[0].id);
          await cargarDetalleProveedor(datosDeuda.proveedores[0].id);
        }
      } catch (error) {
        const mensajeError = error instanceof Error ? error.message : 'Error desconocido';
        setError(mensajeError);
        console.error('Error al cargar datos:', error);
      } finally {
        setCargando(false);
      }
    };

    cargarDatos();
  }, []);

  // Cargar detalle del proveedor cuando cambia el proveedor seleccionado
  const cargarDetalleProveedor = async (idProveedor: string): Promise<void> => {
    try {
      const respuesta = await fetch(`/api/proveedores/detalle/${idProveedor}`);
      if (!respuesta.ok) {
        throw new Error('Error al cargar detalle del proveedor');
      }
      const datos: DetalleProveedor = await respuesta.json();
      setDetalleProveedor(datos);
    } catch (error) {
      console.error('Error al cargar detalle del proveedor:', error);
    }
  };

  const manejarSeleccionProveedor = (idProveedor: string): void => {
    setProveedorSeleccionado(idProveedor);
    cargarDetalleProveedor(idProveedor);
  };

  // Renderizar el tipo de gráfico apropiado
  const renderizarGraficoHistorico = () => {
    if (!datosHistoricos) return null;

    const datos = periodoHistorico === 'weekly'
      ? datosHistoricos.datos_semanales
      : datosHistoricos.datos_mensuales;

    const alturaGrafico = 300;
    const etiquetaPeriodo = periodoHistorico === 'weekly' ? 'Semanal' : 'Mensual';

    const TickPersonalizadoEjeX = (props: any) => {
      const { x, y, payload } = props;
      const item = datos.find(d => d.etiqueta === payload.value);
      const etiqueta = item && periodoHistorico === 'weekly' ? formatearEtiquetaSemana(item) : payload.value;

      return (
        <g transform={`translate(${x},${y})`}>
          <text
            x={0}
            y={0}
            dy={16}
            textAnchor="middle"
            fill="currentColor"
            className="text-muted-foreground"
            style={{ fontSize: '12px' }}
          >
            {etiqueta}
          </text>
        </g>
      );
    };

    switch (tipoGrafico) {
      case 'line':
        return (
          <ResponsiveContainer width="100%" height={alturaGrafico}>
            <LineChart
              data={datos}
              margin={{ top: 5, right: 30, left: 20, bottom: 50 }}
            >
              <CartesianGrid
                strokeDasharray="3 3"
                stroke="currentColor"
                className="text-border dark:text-border opacity-30"
              />
              <XAxis
                dataKey="etiqueta"
                height={60}
                tick={TickPersonalizadoEjeX}
                axisLine={{ stroke: 'currentColor' }}
                tickLine={{ stroke: 'currentColor' }}
                className="text-muted-foreground"
              />
              <YAxis
                tick={{ fill: 'currentColor' }}
                axisLine={{ stroke: 'currentColor' }}
                tickLine={{ stroke: 'currentColor' }}
                className="text-muted-foreground"
              />
              <Tooltip
                formatter={(value) => formatearMoneda(value as number)}
                contentStyle={{
                  backgroundColor: 'hsl(var(--background))',
                  border: '1px solid hsl(var(--border))',
                  borderRadius: '6px',
                  color: 'hsl(var(--foreground))',
                  boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                }}
                labelStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Legend
                wrapperStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Line type="monotone" dataKey="deuda_total" name="Deuda Total" stroke="#8884d8" strokeWidth={2} />
              <Line type="monotone" dataKey="nuevas_facturas" name="Nuevas Facturas" stroke="#82ca9d" strokeWidth={2} />
              <Line type="monotone" dataKey="pagos_realizados" name="Pagos" stroke="#ffc658" strokeWidth={2} />
            </LineChart>
          </ResponsiveContainer>
        );

      case 'area':
        return (
          <ResponsiveContainer width="100%" height={alturaGrafico}>
            <AreaChart
              data={datos}
              margin={{ top: 5, right: 30, left: 20, bottom: 50 }}
            >
              <CartesianGrid
                strokeDasharray="3 3"
                stroke="currentColor"
                className="text-border dark:text-border opacity-30"
              />
              <XAxis
                dataKey="etiqueta"
                height={60}
                tick={TickPersonalizadoEjeX}
                axisLine={{ stroke: 'currentColor' }}
                tickLine={{ stroke: 'currentColor' }}
                className="text-muted-foreground"
              />
              <YAxis
                tick={{ fill: 'currentColor' }}
                axisLine={{ stroke: 'currentColor' }}
                tickLine={{ stroke: 'currentColor' }}
                className="text-muted-foreground"
              />
              <Tooltip
                formatter={(value) => formatearMoneda(value as number)}
                contentStyle={{
                  backgroundColor: 'hsl(var(--background))',
                  border: '1px solid hsl(var(--border))',
                  borderRadius: '6px',
                  color: 'hsl(var(--foreground))',
                  boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                }}
                labelStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Legend
                wrapperStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Area type="monotone" dataKey="deuda_total" name="Deuda Total" stroke="#8884d8" fill="#8884d8" fillOpacity={0.3} />
              <Area type="monotone" dataKey="nuevas_facturas" name="Nuevas Facturas" stroke="#82ca9d" fill="#82ca9d" fillOpacity={0.3} />
              <Area type="monotone" dataKey="pagos_realizados" name="Pagos" stroke="#ffc658" fill="#ffc658" fillOpacity={0.3} />
            </AreaChart>
          </ResponsiveContainer>
        );

      case 'bar':
        return (
          <ResponsiveContainer width="100%" height={alturaGrafico}>
            <BarChart
              data={datos}
              margin={{ top: 5, right: 30, left: 20, bottom: 50 }}
              className="[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700"
            >
              <CartesianGrid
                strokeDasharray="3 3"
                stroke="currentColor"
                className="text-border dark:text-border opacity-30"
              />
              <XAxis
                dataKey="etiqueta"
                height={60}
                tick={TickPersonalizadoEjeX}
                axisLine={{ stroke: 'currentColor' }}
                tickLine={{ stroke: 'currentColor' }}
                className="text-muted-foreground"
              />
              <YAxis
                tick={{ fill: 'currentColor' }}
                axisLine={{ stroke: 'currentColor' }}
                tickLine={{ stroke: 'currentColor' }}
                className="text-muted-foreground"
              />
              <Tooltip
                formatter={(value) => formatearMoneda(value as number)}
                contentStyle={{
                  backgroundColor: 'hsl(var(--background))',
                  border: '1px solid hsl(var(--border))',
                  borderRadius: '6px',
                  color: 'hsl(var(--foreground))',
                  boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                }}
                labelStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Legend
                wrapperStyle={{ color: 'hsl(var(--foreground))' }}
              />
              <Bar dataKey="deuda_total" name="Deuda Total" fill="#8884d8" radius={[2, 2, 0, 0]} />
              <Bar dataKey="nuevas_facturas" name="Nuevas Facturas" fill="#82ca9d" radius={[2, 2, 0, 0]} />
              <Bar dataKey="pagos_realizados" name="Pagos" fill="#ffc658" radius={[2, 2, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        );

      default:
        return null;
    }
  };

  if (cargando) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-background text-foreground">
        <div className="text-xl text-gray-600 dark:text-muted-foreground">Cargando datos del dashboard...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-background text-foreground">
        <div className="text-xl text-red-600 dark:text-destructive">Error: {error}</div>
      </div>
    );
  }

  return (
    <div className="bg-white dark:bg-card rounded-lg shadow-lg p-4 md:p-6 animate-fade-in shadow mt-[50px]">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-800 dark:text-foreground">CERVECERÍA TEMPLE - Distribución de Deuda</h1>
      </div>

      {/* Tarjetas de resumen superior */}
      <div className="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-4 gap-4 md:gap-6 mb-6">
        {datosPasivo && (
          <>
            <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 border dark:border-border">
              <h3 className="text-lg font-semibold text-gray-700 dark:text-foreground mb-2">Deuda Total</h3>
              <p className="text-2xl md:text-3xl font-bold text-blue-600 dark:text-primary">{formatearMoneda(datosPasivo.totales.total_pendiente_pago)}</p>
              <p className="text-sm text-gray-500 dark:text-muted-foreground mt-2">{datosPasivo.conteo.facturas_pendientes_pago} facturas sin pagar</p>
            </div>

            <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 border dark:border-border">
              <h3 className="text-lg font-semibold text-gray-700 dark:text-foreground mb-2">Validadas</h3>
              <p className="text-2xl md:text-3xl font-bold text-green-600 dark:text-dashboard-green">{formatearMoneda(datosPasivo.totales.total_validado)}</p>
              <p className="text-sm text-gray-500 dark:text-muted-foreground mt-2">{datosPasivo.conteo.facturas_validadas} facturas validadas</p>
            </div>

            <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 border dark:border-border">
              <h3 className="text-lg font-semibold text-gray-700 dark:text-foreground mb-2">Pendientes de Validación</h3>
              <p className="text-2xl md:text-3xl font-bold text-amber-600 dark:text-dashboard-amber">{formatearMoneda(datosPasivo.totales.total_por_validar)}</p>
              <p className="text-sm text-gray-500 dark:text-muted-foreground mt-2">{datosPasivo.conteo.facturas_por_validar} facturas por validar</p>
            </div>

            <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 border dark:border-border">
              <h3 className="text-lg font-semibold text-gray-700 dark:text-foreground mb-2">Total Facturas</h3>
              <p className="text-2xl md:text-3xl font-bold text-indigo-600 dark:text-dashboard-indigo">{formatearMoneda(datosPasivo.totales.total_general)}</p>
              <p className="text-sm text-gray-500 dark:text-muted-foreground mt-2">{datosPasivo.conteo.facturas_total} facturas totales</p>
            </div>
          </>
        )}
      </div>

      {/* Contenido principal */}
      {/* <div className="grid grid-cols-1 lg:grid-cols-2 gap-6"> */}
      {/* Columna izquierda */}
      <div className="lg:col-span-3">
        {/* Gráfico histórico con controles */}
        {datosHistoricos && (
          <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 mb-6 border dark:border-border">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <h2 className="text-xl font-bold text-gray-800 dark:text-foreground">Historial de Pagos</h2>
              <div className="flex mt-2 sm:mt-0">
                <div className="mr-4">
                  <label className="text-sm text-gray-600 dark:text-muted-foreground mr-2">Período:</label>
                  <select
                    className="text-sm border dark:border-border bg-white dark:bg-background text-gray-900 dark:text-foreground rounded px-2 py-1"
                    value={periodoHistorico}
                    onChange={(e) => setPeriodoHistorico(e.target.value as 'weekly' | 'monthly')}
                  >
                    <option value="monthly">Mensual</option>
                    <option value="weekly">Semanal</option>
                  </select>
                </div>
                <div>
                  <label className="text-sm text-gray-600 dark:text-muted-foreground mr-2">Gráfico:</label>
                  <select
                    className="text-sm border dark:border-border bg-white dark:bg-background text-gray-900 dark:text-foreground rounded px-2 py-1"
                    value={tipoGrafico}
                    onChange={(e) => setTipoGrafico(e.target.value as 'line' | 'area' | 'bar')}
                  >
                    <option value="line">Línea</option>
                    <option value="area">Área</option>
                    <option value="bar">Barras</option>
                  </select>
                </div>
              </div>
            </div>
            {renderizarGraficoHistorico()}
          </div>
        )}

        {/* Gráfico de tendencias de pago */}
        {datosHistoricos && (
          <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 mb-6 border dark:border-border">
            <h2 className="text-xl font-bold mb-4 text-gray-800 dark:text-foreground">Tendencias de Pago</h2>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart
                data={datosHistoricos.tendencia_pagos}
                margin={{ top: 5, right: 30, left: 20, bottom: 50 }}
                className="[&_.recharts-active-bar]:dark:fill-gray-800 [&_.recharts-tooltip-cursor]:dark:fill-gray-800 [&_.recharts-active-shape]:dark:fill-gray-700"
              >
                <CartesianGrid strokeDasharray="3 3" stroke="currentColor" className="text-border dark:text-border" />
                <XAxis
                  dataKey="etiqueta"
                  height={60}
                  tick={({ x, y, payload }) => (
                    <g transform={`translate(${x},${y})`}>
                      <text
                        x={0}
                        y={0}
                        dy={16}
                        textAnchor="middle"
                        fill="currentColor"
                        className="text-muted-foreground"
                        style={{ fontSize: '12px' }}
                      >
                        {payload.value}
                      </text>
                    </g>
                  )}
                />
                <YAxis tick={{ fill: 'currentColor' }} className="text-muted-foreground" />
                <Tooltip
                  formatter={(value) => formatearMoneda(value as number)}
                  contentStyle={{
                    backgroundColor: 'hsl(var(--background))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '6px',
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Legend />
                <Bar dataKey="nuevas_facturas_monto" name="Nuevas Facturas" fill="#8884d8" />
                <Bar dataKey="pagos_realizados_monto" name="Pagos" fill="#82ca9d" />
                <Bar dataKey="balance_mensual" name="Balance Mensual" fill="#ffc658" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        )}
      </div>

      {/* Columna derecha */}
      <div className='flex items-center justify-between flex-col xl:flex-row'>
        {/* Gráfico de distribución de proveedores */}
        {datosDeuda && (
          <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 mb-6 w-full xl:w-[45%] border dark:border-border">
            <h2 className="text-xl font-bold text-gray-800 dark:text-foreground">Distribución de Deuda</h2>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={datosDeuda.proveedores.slice(0, 5)} // Tomar los 5 principales proveedores
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="deuda"
                  nameKey="nombre"
                  label={({ nombre, percent }) => {
                    // Truncar nombres largos de proveedores para la etiqueta
                    const nombreCorto = nombre.length > 12 ? `${nombre.substring(0, 10)}...` : nombre;
                    return `${nombreCorto}: ${(percent * 100).toFixed(0)}%`;
                  }}
                >
                  {datosDeuda.proveedores.slice(0, 5).map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORES[index % COLORES.length]} />
                  ))}
                </Pie>
                <Tooltip
                  formatter={(value) => formatearMoneda(value as number)}
                  labelFormatter={(name) => `Proveedor: ${name}`}
                  contentStyle={{
                    backgroundColor: 'hsl(var(--background))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '6px',
                    color: 'hsl(var(--foreground))'
                  }}
                  labelStyle={{ color: 'hsl(var(--foreground))' }}
                  itemStyle={{ color: 'hsl(var(--foreground))' }}
                />
              </PieChart>
            </ResponsiveContainer>
            <div className="mt-4">
              <h3 className="font-semibold mb-2 text-gray-800 dark:text-foreground">Principales Proveedores por Deuda</h3>
              <div className="max-h-60 overflow-y-auto">
                {datosDeuda.proveedores.map(proveedor => (
                  <div
                    key={proveedor.id}
                    className={`p-2 mb-1 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-muted ${proveedorSeleccionado === proveedor.id ? 'bg-blue-100 dark:bg-accent' : ''}`}
                    onClick={() => manejarSeleccionProveedor(proveedor.id)}
                  >
                    <div className="flex justify-between items-center">
                      <span className="font-medium truncate mr-2 text-gray-900 dark:text-foreground" title={proveedor.nombre}>
                        {proveedor.nombre.length > 20 ? `${proveedor.nombre.substring(0, 18)}...` : proveedor.nombre}
                      </span>
                      <span className="text-sm whitespace-nowrap text-gray-700 dark:text-foreground">{formatearMoneda(proveedor.deuda)}</span>
                    </div>
                    <div className="text-xs text-gray-500 dark:text-muted-foreground">{proveedor.porcentaje.toFixed(1)}% de la deuda total</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* Detalle del proveedor */}
        {detalleProveedor && (
          <div className="bg-white dark:bg-card rounded-lg shadow p-4 md:p-6 w-full xl:w-[45%] border dark:border-border">
            <h2 className="text-xl font-bold mb-4 text-gray-800 dark:text-foreground truncate" title={detalleProveedor.proveedor.nombre}>
              {detalleProveedor.proveedor.nombre}
            </h2>
            <div className="mb-4 text-gray-700 dark:text-foreground">
              <p className="text-sm"><strong>CUIT:</strong> {detalleProveedor.proveedor.cuit || 'N/A'}</p>
              <p className="text-sm"><strong>Plazo de Crédito:</strong> {detalleProveedor.proveedor.dias_credito || 0} días</p>
              <p className="text-sm"><strong>Promedio de Pago:</strong> {detalleProveedor.tiempo_promedio_pago || 0} días</p>
            </div>

            <div className="mb-4">
              <h3 className="font-semibold mb-2 text-gray-800 dark:text-foreground">Deuda por Antigüedad</h3>
              <div className="grid grid-cols-2 gap-2">
                <div className="border dark:border-border rounded p-2 bg-gray-50 dark:bg-muted">
                  <p className="text-xs text-gray-500 dark:text-muted-foreground">Actual</p>
                  <p className="font-medium text-gray-900 dark:text-foreground">{formatearMoneda(detalleProveedor.deuda_por_antiguedad.current)}</p>
                </div>
                <div className="border dark:border-border rounded p-2 bg-gray-50 dark:bg-muted">
                  <p className="text-xs text-gray-500 dark:text-muted-foreground">1-30 Días</p>
                  <p className="font-medium text-gray-900 dark:text-foreground">{formatearMoneda(detalleProveedor.deuda_por_antiguedad['1_30'])}</p>
                </div>
                <div className="border dark:border-border rounded p-2 bg-gray-50 dark:bg-muted">
                  <p className="text-xs text-gray-500 dark:text-muted-foreground">31-60 Días</p>
                  <p className="font-medium text-gray-900 dark:text-foreground">{formatearMoneda(detalleProveedor.deuda_por_antiguedad['31_60'])}</p>
                </div>
                <div className="border dark:border-border rounded p-2 bg-gray-50 dark:bg-muted">
                  <p className="text-xs text-gray-500 dark:text-muted-foreground">61-90 Días</p>
                  <p className="font-medium text-gray-900 dark:text-foreground">{formatearMoneda(detalleProveedor.deuda_por_antiguedad['61_90'])}</p>
                </div>
                <div className="border dark:border-border rounded p-2 col-span-2 bg-gray-50 dark:bg-muted">
                  <p className="text-xs text-gray-500 dark:text-muted-foreground">Más de 90 Días</p>
                  <p className="font-medium text-gray-900 dark:text-foreground">{formatearMoneda(detalleProveedor.deuda_por_antiguedad.over_90)}</p>
                </div>
              </div>
            </div>

            <div>
              <h3 className="font-semibold mb-2 text-gray-800 dark:text-foreground">Facturas Pendientes ({detalleProveedor.facturas_pendientes_detalle.length})</h3>
              <div className="max-h-60 overflow-y-auto">
                {detalleProveedor.facturas_pendientes_detalle.map(factura => (
                  <div key={factura.id} className="border-b dark:border-border last:border-b-0 py-2">
                    <div className="flex justify-between">
                      <span className="font-medium truncate mr-2 text-gray-900 dark:text-foreground" title={`#${factura.nro_factura || factura.nro_documento}`}>
                        #{factura.nro_factura || factura.nro_documento}
                      </span>
                      <span className="whitespace-nowrap text-gray-700 dark:text-foreground">{formatearMoneda(factura.total)}</span>
                    </div>
                    <div className="text-xs flex justify-between flex-wrap">
                      <span className="text-gray-500 dark:text-muted-foreground">Vence: {new Date(factura.fecha_limite).toLocaleDateString()}</span>
                      <span className={`${factura.dias_vencidos > 0 ? 'text-red-600 dark:text-destructive' : 'text-green-600 dark:text-dashboard-green'}`}>
                        {factura.dias_vencidos > 0 ? `${factura.dias_vencidos} días vencida` : 'No vencida aún'}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
      {/* </div> */}
    </div>
  );
};

export default Proveedores;