import React, { useState, useEffect } from "react";
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from "recharts";

// Tipos de datos
type TipoPeriodo = "diario" | "semanal" | "mensual";

interface Clima {
  id: number;
  valor: number;
  tipo: string;
}

interface Parametros {
  fechaInicio: string;
  fechaFin: string;
  tipoPeriodo: TipoPeriodo;
  local?: number;
  clima?: number;
}

interface DatosGrafico {
  labels: string[];
  datasets: {
    label: string;
    data: number[];
  }[];
}

interface ResultadoAnalisis {
  status: string;
  parametros: {
    fecha_inicio: string;
    fecha_fin: string;
    tipo_periodo: TipoPeriodo;
    local: string;
    clima: string;
  };
  datos_grafico: DatosGrafico;
  datos_completos: any[];
}

interface DatosClima {
  clima: string;
  dias: number;
  total_ventas: number;
  promedio_ventas_por_dia: number;
  ventas_alimentos: number;
  ventas_bebidas: number;
}

interface ResultadoCorrelacion {
  status: string;
  periodo: {
    fecha_inicio: string;
    fecha_fin: string;
  };
  data: DatosClima[];
}

// Componente principal
const PanelAnalisisVentas: React.FC = () => {
  // Estados
  const [climas, setClimas] = useState<Clima[]>([]);
  const [parametros, setParametros] = useState<Parametros>({
    fechaInicio: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().split("T")[0],
    fechaFin: new Date().toISOString().split("T")[0],
    tipoPeriodo: "mensual",
  });
  const [resultadoAnalisis, setResultadoAnalisis] = useState<ResultadoAnalisis | null>(null);
  const [correlacionClimaVentas, setCorrelacionClimaVentas] = useState<ResultadoCorrelacion | null>(null);
  const [cargando, setCargando] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [tipoGrafico, setTipoGrafico] = useState<"linea" | "barra">("linea");
  const [pestanaActiva, setPestanaActiva] = useState<"analisis" | "correlacion">("analisis");

  // Cargar climas al iniciar
  useEffect(() => {
    obtenerClimas();
    // Análisis inicial
    analizarVentas();
  }, []);

  // Obtener lista de climas
  const obtenerClimas = async () => {
    try {
      const respuesta = await fetch("/api/ventas-analisis/climas");
      if (!respuesta.ok) throw new Error("Error al obtener climas");
      
      const datos = await respuesta.json();
      if (datos.status === "success") {
        setClimas(datos.data);
      } else {
        setError("Error en la respuesta del servidor al obtener climas");
      }
    } catch (err) {
      setError(`Error al obtener climas: ${err instanceof Error ? err.message : String(err)}`);
    }
  };

  // Analizar ventas por período, local y clima
  const analizarVentas = async () => {
    setCargando(true);
    setError(null);
    
    try {
      // Construir URL con parámetros
      const params = new URLSearchParams();
      params.append("fecha_inicio", parametros.fechaInicio);
      params.append("fecha_fin", parametros.fechaFin);
      params.append("tipo_periodo", parametros.tipoPeriodo);
      
      if (parametros.local) {
        params.append("local", parametros.local.toString());
      }
      
      if (parametros.clima) {
        params.append("clima", parametros.clima.toString());
      }
      
      const respuesta = await fetch(`/api/ventas-analisis/por-local-periodo-clima?${params}`);
      if (!respuesta.ok) throw new Error("Error al analizar ventas");
      
      const datos = await respuesta.json();
      if (datos.status === "success") {
        setResultadoAnalisis(datos);
      } else {
        setError("Error en la respuesta del servidor");
      }
    } catch (err) {
      setError(`Error al analizar ventas: ${err instanceof Error ? err.message : String(err)}`);
    } finally {
      setCargando(false);
    }
  };

  // Obtener correlación clima-ventas
  const obtenerCorrelacionClimaVentas = async () => {
    setCargando(true);
    setError(null);
    
    try {
      const params = new URLSearchParams();
      params.append("fecha_inicio", parametros.fechaInicio);
      params.append("fecha_fin", parametros.fechaFin);
      
      const respuesta = await fetch(`/api/ventas-analisis/correlacion-clima-ventas?${params}`);
      if (!respuesta.ok) throw new Error("Error al obtener correlación clima-ventas");
      
      const datos = await respuesta.json();
      if (datos.status === "success") {
        setCorrelacionClimaVentas(datos);
      } else {
        setError("Error en la respuesta del servidor");
      }
    } catch (err) {
      setError(`Error al obtener correlación: ${err instanceof Error ? err.message : String(err)}`);
    } finally {
      setCargando(false);
    }
  };

  // Handler para cambios en formulario
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    
    setParametros(prev => ({
      ...prev,
      [name]: value
    }));
  };

  // Handler para envío de formulario
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (pestanaActiva === "analisis") {
      analizarVentas();
    } else {
      obtenerCorrelacionClimaVentas();
    }
  };

  // Handler para cambio de pestaña
  const cambiarPestana = (pestana: "analisis" | "correlacion") => {
    setPestanaActiva(pestana);
    if (pestana === "correlacion" && !correlacionClimaVentas) {
      obtenerCorrelacionClimaVentas();
    }
  };

  // Preparar datos para el gráfico de análisis
  const prepararDatosGrafico = () => {
    if (!resultadoAnalisis) return [];
    
    const { labels, datasets } = resultadoAnalisis.datos_grafico;
    
    return labels.map((label, index) => {
      const punto: any = { nombre: label };
      
      datasets.forEach(dataset => {
        punto[dataset.label] = dataset.data[index];
      });
      
      return punto;
    });
  };

  // Preparar colores para el gráfico
  const colores = [
    "#3B82F6", "#10B981", "#F59E0B", "#EF4444", 
    "#8B5CF6", "#14B8A6", "#6366F1", "#06B6D4"
  ];

  return (
    <div className="p-6 max-w-7xl mx-auto bg-white dark:bg-card rounded-lg shadow-md">
  <h1 className="text-2xl font-bold mb-6 text-gray-800 dark:text-foreground">Panel de Análisis de Ventas</h1>
  
  {/* Pestañas */}
  <div className="mb-6 border-b border-gray-200 dark:border-border">
    <ul className="flex flex-wrap -mb-px">
      <li className="mr-2">
        <button
          className={`inline-block p-4 ${
            pestanaActiva === "analisis"
              ? "text-blue-600 dark:text-primary border-b-2 border-blue-600 dark:border-primary"
              : "text-gray-500 dark:text-muted-foreground hover:text-gray-700 dark:hover:text-foreground"
          }`}
          onClick={() => cambiarPestana("analisis")}
        >
          Análisis por Periodo
        </button>
      </li>
      <li className="mr-2">
        <button
          className={`inline-block p-4 ${
            pestanaActiva === "correlacion"
              ? "text-blue-600 dark:text-primary border-b-2 border-blue-600 dark:border-primary"
              : "text-gray-500 dark:text-muted-foreground hover:text-gray-700 dark:hover:text-foreground"
          }`}
          onClick={() => cambiarPestana("correlacion")}
        >
          Correlación Clima-Ventas
        </button>
      </li>
    </ul>
  </div>
  
  {/* Formulario */}
  <form onSubmit={handleSubmit} className="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div className="mb-4">
      <label className="block text-gray-700 dark:text-foreground text-sm font-bold mb-2" htmlFor="fechaInicio">
        Fecha Inicio
      </label>
      <input
        type="date"
        id="fechaInicio"
        name="fechaInicio"
        value={parametros.fechaInicio}
        onChange={handleInputChange}
        className="shadow appearance-none border dark:border-border rounded w-full py-2 px-3 text-gray-700 dark:text-foreground dark:bg-input leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
        required
      />
    </div>
    
    <div className="mb-4">
      <label className="block text-gray-700 dark:text-foreground text-sm font-bold mb-2" htmlFor="fechaFin">
        Fecha Fin
      </label>
      <input
        type="date"
        id="fechaFin"
        name="fechaFin"
        value={parametros.fechaFin}
        onChange={handleInputChange}
        className="shadow appearance-none border dark:border-border rounded w-full py-2 px-3 text-gray-700 dark:text-foreground dark:bg-input leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
        required
      />
    </div>
    
    {pestanaActiva === "analisis" && (
      <>
        <div className="mb-4">
          <label className="block text-gray-700 dark:text-foreground text-sm font-bold mb-2" htmlFor="tipoPeriodo">
            Tipo de Periodo
          </label>
          <select
            id="tipoPeriodo"
            name="tipoPeriodo"
            value={parametros.tipoPeriodo}
            onChange={handleInputChange}
            className="shadow appearance-none border dark:border-border rounded w-full py-2 px-3 text-gray-700 dark:text-foreground dark:bg-input leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
          >
            <option value="diario">Diario</option>
            <option value="semanal">Semanal</option>
            <option value="mensual">Mensual</option>
          </select>
        </div>
        
        <div className="mb-4">
          <label className="block text-gray-700 dark:text-foreground text-sm font-bold mb-2" htmlFor="local">
            Local
          </label>
          <select
            id="local"
            name="local"
            value={parametros.local || ""}
            onChange={handleInputChange}
            className="shadow appearance-none border dark:border-border rounded w-full py-2 px-3 text-gray-700 dark:text-foreground dark:bg-input leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
          >
            <option value="">Todos</option>
            <option value="1">Temple 1</option>
            <option value="2">Temple 2</option>
          </select>
        </div>
        
        <div className="mb-4">
          <label className="block text-gray-700 dark:text-foreground text-sm font-bold mb-2" htmlFor="clima">
            Clima
          </label>
          <select
            id="clima"
            name="clima"
            value={parametros.clima || ""}
            onChange={handleInputChange}
            className="shadow appearance-none border dark:border-border rounded w-full py-2 px-3 text-gray-700 dark:text-foreground dark:bg-input leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
          >
            <option value="">Todos</option>
            {climas.map(clima => (
              <option key={clima.id} value={clima.valor}>
                {clima.tipo}
              </option>
            ))}
          </select>
        </div>
        
        <div className="mb-4">
          <label className="block text-gray-700 dark:text-foreground text-sm font-bold mb-2" htmlFor="tipoGrafico">
            Tipo de Gráfico
          </label>
          <select
            id="tipoGrafico"
            name="tipoGrafico"
            value={tipoGrafico}
            onChange={(e) => setTipoGrafico(e.target.value as "linea" | "barra")}
            className="shadow appearance-none border dark:border-border rounded w-full py-2 px-3 text-gray-700 dark:text-foreground dark:bg-input leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
          >
            <option value="linea">Línea</option>
            <option value="barra">Barra</option>
          </select>
        </div>
      </>
    )}
    
    <div className="col-span-1 md:col-span-2 lg:col-span-3 flex justify-end">
      <button
        type="submit"
        className="bg-blue-500 dark:bg-primary hover:bg-blue-700 dark:hover:bg-primary/90 text-white dark:text-primary-foreground font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-ring"
        disabled={cargando}
      >
        {cargando ? "Cargando..." : "Analizar"}
      </button>
    </div>
  </form>
  
  {/* Mostrar error si existe */}
  {error && (
    <div className="bg-red-100 dark:bg-destructive/20 border border-red-400 dark:border-destructive text-red-700 dark:text-destructive-foreground px-4 py-3 rounded relative mb-6" role="alert">
      <strong className="font-bold">Error: </strong>
      <span className="block sm:inline">{error}</span>
    </div>
  )}
  
  {/* Resultados - Análisis por periodo */}
  {pestanaActiva === "analisis" && resultadoAnalisis && (
    <div className="mt-8">
      <h2 className="text-xl font-semibold mb-4 text-foreground dark:text-foreground">
        Análisis de Ventas ({resultadoAnalisis.parametros.fecha_inicio} a {resultadoAnalisis.parametros.fecha_fin})
      </h2>
      
      <div className="mb-4 p-4 bg-gray-50 dark:bg-muted rounded-lg">
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Parámetros</h3>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div>
            <span className="font-semibold text-foreground dark:text-foreground">Periodo:</span> {resultadoAnalisis.parametros.tipo_periodo}
          </div>
          <div>
            <span className="font-semibold text-foreground dark:text-foreground">Local:</span> {resultadoAnalisis.parametros.local}
          </div>
          <div>
            <span className="font-semibold text-foreground dark:text-foreground">Clima:</span> {resultadoAnalisis.parametros.clima}
          </div>
        </div>
      </div>
      
      <div className="overflow-x-auto">
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Datos Detallados</h3>
        <table className="min-w-full bg-white dark:bg-card border border-gray-300 dark:border-border">
          <thead>
            <tr>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Periodo</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Local</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Clima</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Ventas Totales</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Alimentos</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Bebidas</th>
            </tr>
          </thead>
          <tbody>
            {resultadoAnalisis.datos_completos.map((dato, index) => (
              <tr key={index} className={index % 2 === 0 ? "bg-gray-50 dark:bg-muted/50" : "bg-white dark:bg-card"}>
                <td className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">{dato.periodo}</td>
                <td className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">{dato.local}</td>
                <td className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">{dato.clima}</td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.total_ventas.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.ventas_alimentos.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.ventas_bebidas.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )}
  
  {/* Resultados - Correlación Clima-Ventas */}
  {pestanaActiva === "correlacion" && correlacionClimaVentas && (
    <div className="mt-8">
      <h2 className="text-xl font-semibold mb-4 text-foreground dark:text-foreground">
        Correlación Clima-Ventas ({correlacionClimaVentas.periodo.fecha_inicio} a {correlacionClimaVentas.periodo.fecha_fin})
      </h2>
      
      <div className="overflow-x-auto">
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Detalle por Clima</h3>
        <table className="min-w-full bg-white dark:bg-card border border-gray-300 dark:border-border">
          <thead>
            <tr>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Clima</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Días</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Ventas Totales</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Promedio Diario</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Alimentos</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Bebidas</th>
              <th className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">Ratio A/B</th>
            </tr>
          </thead>
          <tbody>
            {correlacionClimaVentas.data.map((dato, index) => (
              <tr key={index} className={index % 2 === 0 ? "bg-gray-50 dark:bg-muted/50" : "bg-white dark:bg-card"}>
                <td className="py-2 px-4 border-b dark:border-border text-foreground dark:text-foreground">{dato.clima}</td>
                <td className="py-2 px-4 border-b dark:border-border text-center text-foreground dark:text-foreground">{dato.dias}</td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.total_ventas.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.promedio_ventas_por_dia.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.ventas_alimentos.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  ${dato.ventas_bebidas.toLocaleString(undefined, {minimumFractionDigits: 2})}
                </td>
                <td className="py-2 px-4 border-b dark:border-border text-right text-foreground dark:text-foreground">
                  {(dato.ventas_alimentos / dato.ventas_bebidas).toFixed(2)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      
      <div className="mt-8 p-4 bg-blue-50 dark:bg-accent/20 rounded-lg">
        <h3 className="text-lg font-medium mb-2 text-foreground dark:text-foreground">Insights</h3>
        <ul className="list-disc pl-6 text-foreground dark:text-foreground">
          {correlacionClimaVentas.data.length > 0 && (
            <>
              <li className="mb-2">
                El clima con mayores ventas totales es <strong>{correlacionClimaVentas.data[0].clima}</strong> con ${correlacionClimaVentas.data[0].total_ventas.toLocaleString()} en ventas.
              </li>
              
              {correlacionClimaVentas.data
                .sort((a, b) => b.promedio_ventas_por_dia - a.promedio_ventas_por_dia)[0] && (
                <li className="mb-2">
                  El clima con mayor promedio de ventas diarias es <strong>
                    {correlacionClimaVentas.data.sort((a, b) => b.promedio_ventas_por_dia - a.promedio_ventas_por_dia)[0].clima}
                  </strong> con ${correlacionClimaVentas.data.sort((a, b) => b.promedio_ventas_por_dia - a.promedio_ventas_por_dia)[0].promedio_ventas_por_dia.toLocaleString()} por día.
                </li>
              )}
              
              {correlacionClimaVentas.data
                .sort((a, b) => (b.ventas_alimentos / b.ventas_bebidas) - (a.ventas_alimentos / a.ventas_bebidas))[0] && (
                <li className="mb-2">
                  El clima donde se venden proporcionalmente más alimentos que bebidas es <strong>
                    {correlacionClimaVentas.data.sort((a, b) => (b.ventas_alimentos / b.ventas_bebidas) - (a.ventas_alimentos / a.ventas_bebidas))[0].clima}
                  </strong>.
                </li>
              )}
            </>
          )}
          <li className="mb-2">
            Esta información puede ser útil para planificar el inventario y el personal según las previsiones meteorológicas.
          </li>
        </ul>
      </div>
    </div>
  )}
</div>
  );
};

export default PanelAnalisisVentas;