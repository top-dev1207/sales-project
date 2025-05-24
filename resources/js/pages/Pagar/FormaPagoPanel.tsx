import React, { useState, useEffect } from 'react';
import { PlusCircle, Edit, Trash, AlertCircle, CheckCircle, Moon, Sun } from 'lucide-react';

// Define interfaces for our data
interface FormaPago {
  id: number;
  tipo: string;
  estado: number;
  fiscal: number;
  opciones: number;
  local_id?: number;
  fecha_inicio?: string;
  fecha_fin?: string;
}

interface FormValues {
  tipo: string;
  estado: number;
  fiscal: number;
  opciones: number;
  local_id?: string | number;
  periodo_inicio?: string;
  periodo_fin?: string;
}

const FormaPagoPanel: React.FC = () => {
  // Estados
  const [formasPago, setFormasPago] = useState<FormaPago[]>([]);
  const [formaPagoSeleccionada, setFormaPagoSeleccionada] = useState<FormaPago | null>(null);
  const [modoEdicion, setModoEdicion] = useState<boolean>(false);
  const [cargando, setCargando] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [exito, setExito] = useState<string | null>(null);
  const [darkMode, setDarkMode] = useState<boolean>(false);
  const [formValues, setFormValues] = useState<FormValues>({
    tipo: '',
    estado: 1,
    fiscal: 0,
    opciones: 0
  });
  // Initialize dark mode from localStorage or system preference
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
      setDarkMode(true);
      document.documentElement.classList.add('dark');
    }
  }, []);

  // Save theme preference
  useEffect(() => {
    localStorage.setItem('theme', darkMode ? 'dark' : 'light');
  }, [darkMode]);

  // Obtener formas de pago al cargar el componente
  useEffect(() => {
    obtenerFormasPago();
  }, []);

  // Función para obtener todas las formas de pago
  const obtenerFormasPago = async (): Promise<void> => {
    setCargando(true);
    setError(null);
    
    try {
      const response = await fetch('/api/formas-pago');
      
      if (!response.ok) {
        throw new Error('Error al cargar las formas de pago');
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setFormasPago(data.data);
      } else {
        throw new Error(data.message || 'Error al cargar las formas de pago');
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Error desconocido');
      console.error('Error:', err);
    } finally {
      setCargando(false);
    }
  };

  // Función para manejar cambios en el formulario
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>): void => {
    const { name, value, type } = e.target;
    const checked = (e.target as HTMLInputElement).checked;
    
    // Convertir valores numéricos si es necesario
    const finalValue = type === 'checkbox' 
      ? checked ? 1 : 0 
      : (name === 'estado' || name === 'fiscal' || name === 'opciones') 
        ? parseInt(value, 10) 
        : value;
    
    setFormValues({
      ...formValues,
      [name]: finalValue
    });
  };

  // Función para seleccionar una forma de pago para editar
  const seleccionarParaEditar = (formaPago: FormaPago): void => {
    setFormaPagoSeleccionada(formaPago);
    setFormValues({
      tipo: formaPago.tipo,
      estado: formaPago.estado,
      fiscal: formaPago.fiscal,
      opciones: formaPago.opciones,
      local_id: formaPago.local_id || '',
      periodo_inicio: formaPago.fecha_inicio || '',
      periodo_fin: formaPago.fecha_fin || ''
    });
    setModoEdicion(true);
  };

  // Función para crear nueva forma de pago
  const iniciarNueva = (): void => {
    setFormaPagoSeleccionada(null);
    setFormValues({
      tipo: '',
      estado: 1,
      fiscal: 0,
      opciones: 0,
      local_id: '',
      periodo_inicio: '',
      periodo_fin: ''
    });
    setModoEdicion(true);
  };

  // Función para guardar forma de pago (crear o actualizar)
  const guardarFormaPago = async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
    e.preventDefault();
    setError(null);
    setExito(null);
    
    try {
      const url = formaPagoSeleccionada 
        ? `/api/formas-pago/${formaPagoSeleccionada.id}` 
        : '/api/formas-pago';
      
      const method = formaPagoSeleccionada ? 'PUT' : 'POST';
      
      const csrfElement = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = csrfElement ? csrfElement.getAttribute('content') : '';
      
      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken || ''
        },
        body: JSON.stringify(formValues)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Error al guardar la forma de pago');
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setExito(data.message || 'Forma de pago guardada correctamente');
        obtenerFormasPago();
        setModoEdicion(false);
      } else {
        throw new Error(data.message || 'Error al guardar la forma de pago');
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Error desconocido');
      console.error('Error:', err);
    }
  };

  // Función para eliminar una forma de pago
  const eliminarFormaPago = async (id: number): Promise<void> => {
    if (!confirm('¿Está seguro que desea desactivar esta forma de pago?')) {
      return;
    }
    
    setError(null);
    setExito(null);
    
    try {
      const csrfElement = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = csrfElement ? csrfElement.getAttribute('content') : '';
      
      const response = await fetch(`/api/formas-pago/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Error al desactivar la forma de pago');
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setExito(data.message || 'Forma de pago desactivada correctamente');
        obtenerFormasPago();
      } else {
        throw new Error(data.message || 'Error al desactivar la forma de pago');
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Error desconocido');
      console.error('Error:', err);
    }
  };

  // Función para cancelar la edición
  const cancelarEdicion = (): void => {
    setModoEdicion(false);
    setFormaPagoSeleccionada(null);
  };

  // Renderizado de opciones como texto legible
  const renderizarOpciones = (opciones: number): string => {
    const caracteristicas: string[] = [];
    
    if (opciones & 1) {
      caracteristicas.push('Impacta en caja');
    } else {
      caracteristicas.push('No impacta en caja');
    }
    
    if (opciones & 2) {
      caracteristicas.push('Medio electrónico');
    } else {
      caracteristicas.push('Medio no electrónico');
    }
    
    return caracteristicas.join(', ');
  };

  return (
    <div className="min-h-screen bg-background text-foreground transition-colors duration-300">
      <div className="container mx-auto px-4 py-6">
        {/* Mensajes de error o éxito */}
        {error && (
          <div className="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg mb-4 flex items-center">
            <AlertCircle className="w-5 h-5 mr-2" />
            <span>{error}</span>
          </div>
        )}
        
        {exito && (
          <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-4 py-3 rounded-lg mb-4 flex items-center">
            <CheckCircle className="w-5 h-5 mr-2" />
            <span>{exito}</span>
          </div>
        )}
        
        {/* Botón para agregar nueva forma de pago */}
        {!modoEdicion && (
          <button 
            onClick={iniciarNueva} 
            className="bg-primary hover:bg-primary/90 text-primary-foreground px-4 py-2 rounded-lg flex items-center mb-4 transition-colors duration-200"
          >
            <PlusCircle className="w-5 h-5 mr-2" />
            Nueva Forma de Pago
          </button>
        )}

        {!modoEdicion && (
          <>
            {cargando ? (
              <div className="text-center py-8">
                <div className="animate-spin rounded-full h-12 w-12 border-2 border-primary border-t-transparent mx-auto"></div>
                <p className="mt-4 text-muted-foreground">Cargando formas de pago...</p>
              </div>
            ) : formasPago.length > 0 ? (
              <div className="overflow-x-auto rounded-lg border border-border bg-card shadow-sm">
                <table className="min-w-full">
                  <thead className="bg-muted/50">
                    <tr>
                      <th className="py-3 px-4 text-left text-sm font-medium text-muted-foreground">ID</th>
                      <th className="py-3 px-4 text-left text-sm font-medium text-muted-foreground">Nombre</th>
                      <th className="py-3 px-4 text-left text-sm font-medium text-muted-foreground">Estado</th>
                      <th className="py-3 px-4 text-left text-sm font-medium text-muted-foreground">Fiscal</th>
                      <th className="py-3 px-4 text-left text-sm font-medium text-muted-foreground">Opciones</th>
                      <th className="py-3 px-4 text-left text-sm font-medium text-muted-foreground">Acciones</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border">
                    {formasPago.map((formaPago) => (
                      <tr key={formaPago.id} className="hover:bg-muted/25 transition-colors duration-150">
                        <td className="py-3 px-4 text-sm">{formaPago.id}</td>
                        <td className="py-3 px-4 text-sm font-medium">{formaPago.tipo}</td>
                        <td className="py-3 px-4">
                          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            formaPago.estado 
                              ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400' 
                              : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                          }`}>
                            {formaPago.estado ? 'Activo' : 'Inactivo'}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            formaPago.fiscal 
                              ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' 
                              : 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400'
                          }`}>
                            {formaPago.fiscal ? 'Fiscal' : 'No Fiscal'}
                          </span>
                        </td>
                        <td className="py-3 px-4 text-sm text-muted-foreground">
                          {renderizarOpciones(formaPago.opciones)}
                        </td>
                        <td className="py-3 px-4">
                          <div className="flex space-x-2">
                            <button
                              onClick={() => seleccionarParaEditar(formaPago)}
                              className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-150"
                              title="Editar"
                            >
                              <Edit className="w-4 h-4" />
                            </button>
                            <button
                              onClick={() => eliminarFormaPago(formaPago.id)}
                              className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-150"
                              title="Eliminar"
                            >
                              <Trash className="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            ) : (
              <div className="text-center py-8 bg-muted/10 rounded-lg border border-dashed border-muted-foreground/25">
                <p className="text-muted-foreground">No hay formas de pago disponibles.</p>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default FormaPagoPanel;