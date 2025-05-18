# API de Análisis de Empleados

Esta API proporciona endpoints para analizar el desempeño y la información de los empleados en el sistema de ventas.

## Endpoints disponibles

### 1. Obtener empleados activos

Devuelve una lista de todos los empleados actualmente activos en el sistema con información básica.

**URL**: `/api/empleados-analisis/activos`

**Método**: `GET`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "legajo": 100,
      "nombre_completo": "Juan Pérez",
      "puesto": "Vendedor",
      "email": "juan.perez@ejemplo.com",
      "fecha_ingreso": "2022-01-10",
      "antiguedad": 18,
      "salario": 150000,
      "estado": "Activo"
    },
    {
      "id": 2,
      "legajo": 101,
      "nombre_completo": "María López",
      "puesto": "Gerente",
      "email": "maria.lopez@ejemplo.com",
      "fecha_ingreso": "2020-05-15",
      "antiguedad": 36,
      "salario": 250000,
      "estado": "Activo"
    }
  ]
}
```

### 2. Obtener rendimiento de empleados

Devuelve datos de rendimiento de todos los empleados activos para un período determinado.

**URL**: `/api/empleados-analisis/rendimiento`

**Método**: `GET`

**Parámetros opcionales**:

- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)

**Ejemplo**: `/api/empleados-analisis/rendimiento?fecha_inicio=2023-01-01&fecha_fin=2023-03-31`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "periodo": {
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-03-31"
  },
  "data": [
    {
      "empleado_id": 1,
      "legajo": 100,
      "nombre_completo": "Juan Pérez",
      "puesto": "Vendedor",
      "ventas_realizadas": 1250000.00,
      "dias_laborables": 65,
      "productividad_diaria": 19230.77,
      "total_salario_periodo": 450000.00,
      "relacion_costo_beneficio": 2.78
    },
    {
      "empleado_id": 2,
      "legajo": 101,
      "nombre_completo": "María López",
      "puesto": "Gerente",
      "ventas_realizadas": 2500000.00,
      "dias_laborables": 65,
      "productividad_diaria": 38461.54,
      "total_salario_periodo": 750000.00,
      "relacion_costo_beneficio": 3.33
    }
  ]
}
```

### 3. Obtener historial de desempeño de un empleado

Devuelve el historial de desempeño de un empleado específico a lo largo del tiempo.

**URL**: `/api/empleados-analisis/historial/{empleadoId}`

**Método**: `GET`

**Parámetros de ruta**:
- `empleadoId` - ID del empleado

**Parámetros opcionales**:
- `periodo` - Tipo de agrupación: mensual, trimestral, anual (default: mensual)
- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)

**Ejemplo**: `/api/empleados-analisis/historial/1?periodo=mensual&fecha_inicio=2023-01-01&fecha_fin=2023-12-31`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "empleado": {
    "id": 1,
    "legajo": 100,
    "nombre_completo": "Juan Pérez",
    "puesto": "Vendedor"
  },
  "periodo_analisis": {
    "tipo": "mensual",
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-12-31"
  },
  "data": [
    {
      "periodo": "2023-01",
      "fecha_inicio": "2023-01-01",
      "fecha_fin": "2023-01-31",
      "ventas_total": 350000.00,
      "cantidad_ventas": 45,
      "salario": 150000.00,
      "horas_extras": 10000.00,
      "premios": 5000.00,
      "relacion_costo_beneficio": 2.33
    },
    {
      "periodo": "2023-02",
      "fecha_inicio": "2023-02-01",
      "fecha_fin": "2023-02-28",
      "ventas_total": 420000.00,
      "cantidad_ventas": 52,
      "salario": 150000.00,
      "horas_extras": 15000.00,
      "premios": 10000.00,
      "relacion_costo_beneficio": 2.80
    }
  ]
}
```

### 4. Obtener comparativa de empleados

Devuelve una comparativa de rendimiento entre todos los empleados, agrupados por puesto.

**URL**: `/api/empleados-analisis/comparativa`

**Método**: `GET`

**Parámetros opcionales**:
- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)

**Ejemplo**: `/api/empleados-analisis/comparativa?fecha_inicio=2023-01-01&fecha_fin=2023-03-31`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "periodo": {
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-03-31"
  },
  "data": {
    "Vendedor": {
      "empleados": [
        {
          "id": 1,
          "legajo": 100,
          "nombre_completo": "Juan Pérez",
          "salario": 450000.00,
          "ventas": 1250000.00,
          "eficiencia": 2.78
        },
        {
          "id": 3,
          "legajo": 102,
          "nombre_completo": "Pedro Gómez",
          "salario": 450000.00,
          "ventas": 1100000.00,
          "eficiencia": 2.44
        }
      ],
      "promedio_ventas": 1175000.00,
      "promedio_salario": 450000.00,
      "promedio_eficiencia": 2.61
    },
    "Gerente": {
      "empleados": [
        {
          "id": 2,
          "legajo": 101,
          "nombre_completo": "María López",
          "salario": 750000.00,
          "ventas": 2500000.00,
          "eficiencia": 3.33
        }
      ],
      "promedio_ventas": 2500000.00,
      "promedio_salario": 750000.00,
      "promedio_eficiencia": 3.33
    }
  }
}
```

## Códigos de error

- `404` - Recurso no encontrado
- `500` - Error interno del servidor

## Notas

- Todas las fechas deben proporcionarse en formato ISO: `YYYY-MM-DD`
- Los valores monetarios se expresan en la moneda local
- La relación costo/beneficio se calcula como: ventas totales / salario total
- La productividad diaria se calcula como: ventas totales / días laborables 