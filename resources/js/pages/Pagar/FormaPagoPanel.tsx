import React, { useState, useEffect } from 'react';
import { PlusCircle, Edit, Trash, AlertCircle, CheckCircle } from 'lucide-react';

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
  const [formValues, setFormValues] = useState<FormValues>({
    tipo: '',
    estado: 1,
    fiscal: 0,
    opciones: 0
  });

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
    
    if (opciones & 1) { // Bitwise AND para verificar si el bit 1 está activado
      caracteristicas.push('Impacta en caja');
    } else {
      caracteristicas.push('No impacta en caja');
    }
    
    if (opciones & 2) { // Bitwise AND para verificar si el bit 2 está activado
      caracteristicas.push('Medio electrónico');
    } else {
      caracteristicas.push('Medio no electrónico');
    }
    
    return caracteristicas.join(', ');
  };

  return (
    <div className="container mx-auto px-4 py-6">
      <h1 className="text-2xl font-bold mb-6">Gestión de Formas de Pago</h1>
      
      {/* Mensajes de error o éxito */}
      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 flex items-center">
          <AlertCircle className="w-5 h-5 mr-2" />
          <span>{error}</span>
        </div>
      )}
      
      {exito && (
        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 flex items-center">
          <CheckCircle className="w-5 h-5 mr-2" />
          <span>{exito}</span>
        </div>
      )}
      
      {/* Botón para agregar nueva forma de pago */}
      {!modoEdicion && (
        <button 
          onClick={iniciarNueva} 
          className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center mb-4"
        >
          <PlusCircle className="w-5 h-5 mr-2" />
          Nueva Forma de Pago
        </button>
      )}
      
      {/* Formulario de edición */}
      {/* {modoEdicion && (
        <div className="bg-white shadow-md rounded p-4 mb-6">
          <h2 className="text-xl font-semibold mb-4">
            {formaPagoSeleccionada ? 'Editar Forma de Pago' : 'Nueva Forma de Pago'}
          </h2>
          
          <form onSubmit={guardarFormaPago}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2" htmlFor="tipo">
                  Nombre de la Forma de Pago
                </label>
                <input
                  type="text"
                  id="tipo"
                  name="tipo"
                  value={formValues.tipo}
                  onChange={handleInputChange}
                  className="border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                />
              </div>
              
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2" htmlFor="estado">
                  Estado
                </label>
                <select
                  id="estado"
                  name="estado"
                  value={formValues.estado}
                  onChange={handleInputChange}
                  className="border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
                  <option value={1}>Activo</option>
                  <option value={0}>Inactivo</option>
                </select>
              </div>
              
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2" htmlFor="fiscal">
                  Tipo Fiscal
                </label>
                <select
                  id="fiscal"
                  name="fiscal"
                  value={formValues.fiscal}
                  onChange={handleInputChange}
                  className="border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
                  <option value={1}>Fiscal (Blanco)</option>
                  <option value={0}>No Fiscal (Negro)</option>
                </select>
              </div>
              
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2">
                  Opciones
                </label>
                <div className="flex flex-col space-y-2">
                  <label className="inline-flex items-center">
                    <input
                      type="checkbox"
                      name="impactaEnCaja"
                      checked={!!(formValues.opciones & 1)}
                      onChange={(e) => {
                        const newOpciones = e.target.checked 
                          ? formValues.opciones | 1  // Set bit 1
                          : formValues.opciones & ~1; // Clear bit 1
                        setFormValues({
                          ...formValues,
                          opciones: newOpciones
                        });
                      }}
                      className="form-checkbox h-5 w-5 text-blue-600"
                    />
                    <span className="ml-2 text-gray-700">Impacta en caja</span>
                  </label>
                  
                  <label className="inline-flex items-center">
                    <input
                      type="checkbox"
                      name="medioElectronico"
                      checked={!!(formValues.opciones & 2)}
                      onChange={(e) => {
                        const newOpciones = e.target.checked 
                          ? formValues.opciones | 2  // Set bit 2
                          : formValues.opciones & ~2; // Clear bit 2
                        setFormValues({
                          ...formValues,
                          opciones: newOpciones
                        });
                      }}
                      className="form-checkbox h-5 w-5 text-blue-600"
                    />
                    <span className="ml-2 text-gray-700">Medio electrónico</span>
                  </label>
                </div>
              </div>
              
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2" htmlFor="local_id">
                  ID Local (Opcional)
                </label>
                <input
                  type="number"
                  id="local_id"
                  name="local_id"
                  value={formValues.local_id}
                  onChange={handleInputChange}
                  className="border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>
              
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2" htmlFor="periodo_inicio">
                  Fecha Inicio (Opcional)
                </label>
                <input
                  type="date"
                  id="periodo_inicio"
                  name="periodo_inicio"
                  value={formValues.periodo_inicio}
                  onChange={handleInputChange}
                  className="border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>
              
              <div className="mb-4">
                <label className="block text-gray-700 font-bold mb-2" htmlFor="periodo_fin">
                  Fecha Fin (Opcional)
                </label>
                <input
                  type="date"
                  id="periodo_fin"
                  name="periodo_fin"
                  value={formValues.periodo_fin}
                  onChange={handleInputChange}
                  className="border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>
            </div>
            
            <div className="flex justify-end mt-4 space-x-2">
              <button
                type="button"
                onClick={cancelarEdicion}
                className="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded"
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded"
              >
                Guardar
              </button>
            </div>
          </form>
        </div>
      )} */}
      
      {/* Tabla de formas de pago */}
      {!modoEdicion && (
        <>
          {cargando ? (
            <div className="text-center py-4">
              <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500 mx-auto"></div>
              <p className="mt-2">Cargando formas de pago...</p>
            </div>
          ) : formasPago.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white shadow-md rounded">
                <thead className="bg-gray-200 text-gray-700">
                  <tr>
                    <th className="py-3 px-4 text-left">ID</th>
                    <th className="py-3 px-4 text-left">Nombre</th>
                    <th className="py-3 px-4 text-left">Estado</th>
                    <th className="py-3 px-4 text-left">Fiscal</th>
                    <th className="py-3 px-4 text-left">Opciones</th>
                    <th className="py-3 px-4 text-left">Acciones</th>
                  </tr>
                </thead>
                <tbody className="text-gray-600">
                  {formasPago.map((formaPago) => (
                    <tr key={formaPago.id} className="border-b hover:bg-gray-50">
                      <td className="py-3 px-4">{formaPago.id}</td>
                      <td className="py-3 px-4">{formaPago.tipo}</td>
                      <td className="py-3 px-4">
                        <span className={`px-2 py-1 rounded text-white ${formaPago.estado ? 'bg-green-500' : 'bg-red-500'}`}>
                          {formaPago.estado ? 'Activo' : 'Inactivo'}
                        </span>
                      </td>
                      <td className="py-3 px-4">
                        <span className={`px-2 py-1 rounded text-white ${formaPago.fiscal ? 'bg-blue-500' : 'bg-yellow-500'}`}>
                          {formaPago.fiscal ? 'Fiscal' : 'No Fiscal'}
                        </span>
                      </td>
                      <td className="py-3 px-4">
                        {renderizarOpciones(formaPago.opciones)}
                      </td>
                      <td className="py-3 px-4">
                        <div className="flex space-x-2">
                          <button
                            onClick={() => seleccionarParaEditar(formaPago)}
                            className="text-blue-500 hover:text-blue-700"
                            title="Editar"
                          >
                            <Edit className="w-5 h-5" />
                          </button>
                          <button
                            onClick={() => eliminarFormaPago(formaPago.id)}
                            className="text-red-500 hover:text-red-700"
                            title="Eliminar"
                          >
                            <Trash className="w-5 h-5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="text-center py-4 bg-gray-100 rounded">
              <p>No hay formas de pago disponibles.</p>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default FormaPagoPanel;