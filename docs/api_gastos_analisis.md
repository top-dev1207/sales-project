# API de Análisis de Gastos

Esta API proporciona endpoints para analizar los gastos de la empresa en relación con las ventas y categorías de gastos.

## Endpoints disponibles

### 1. Gastos sobre ventas

Devuelve el porcentaje que representa cada rubro de gasto sobre las ventas totales.

**URL**: `/api/gastos-analisis/sobre-ventas`

**Método**: `GET`

**Parámetros opcionales**:

- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)

**Ejemplo**: `/api/gastos-analisis/sobre-ventas?fecha_inicio=2023-01-01&fecha_fin=2023-01-31`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "periodo": {
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-01-31"
  },
  "ventas_totales": 1250000.00,
  "data": [
    {
      "rubro_id": 1,
      "rubro_nombre": "Alimentos",
      "importe": 125000.00,
      "porcentaje_ventas": 10.00
    },
    {
      "rubro_id": 2,
      "rubro_nombre": "Bebidas",
      "importe": 87500.00,
      "porcentaje_ventas": 7.00
    }
  ]
}
```

### 2. Gastos sobre total de gastos

Devuelve el porcentaje que representa cada rubro de gasto sobre los gastos totales.

**URL**: `/api/gastos-analisis/sobre-total`

**Método**: `GET`

**Parámetros opcionales**:

- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)

**Ejemplo**: `/api/gastos-analisis/sobre-total?fecha_inicio=2023-01-01&fecha_fin=2023-01-31`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "periodo": {
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-01-31"
  },
  "gastos_totales": 450000.00,
  "data": [
    {
      "rubro_id": 1,
      "rubro_nombre": "Alimentos",
      "importe": 125000.00,
      "porcentaje_gastos_totales": 27.78
    },
    {
      "rubro_id": 2,
      "rubro_nombre": "Bebidas",
      "importe": 87500.00,
      "porcentaje_gastos_totales": 19.44
    }
  ]
}
```

### 3. Gastos más relevantes

Devuelve los 5 rubros de gastos con mayor importe en el período seleccionado.

**URL**: `/api/gastos-analisis/mas-relevantes`

**Método**: `GET`

**Parámetros opcionales**:

- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)
- `limit` - Cantidad de rubros a mostrar (por defecto: 5, máximo: 20)

**Ejemplo**: `/api/gastos-analisis/mas-relevantes?fecha_inicio=2023-01-01&fecha_fin=2023-01-31&limit=3`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "periodo": {
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-01-31"
  },
  "gastos_totales": 450000.00,
  "data": [
    {
      "rubro_id": 1,
      "rubro_nombre": "Alimentos",
      "importe": 125000.00,
      "porcentaje_gastos_totales": 27.78
    },
    {
      "rubro_id": 2,
      "rubro_nombre": "Bebidas",
      "importe": 87500.00,
      "porcentaje_gastos_totales": 19.44
    },
    {
      "rubro_id": 3,
      "rubro_nombre": "Luz",
      "importe": 65000.00,
      "porcentaje_gastos_totales": 14.44
    }
  ]
}
```

### 4. Dashboard de gastos

Devuelve todos los indicadores anteriores en una sola llamada para facilitar la construcción de un dashboard.

**URL**: `/api/gastos-analisis/dashboard`

**Método**: `GET`

**Parámetros opcionales**:

- `fecha_inicio` - Fecha de inicio del período para el análisis (formato YYYY-MM-DD)
- `fecha_fin` - Fecha fin del período para el análisis (formato YYYY-MM-DD)

**Ejemplo**: `/api/gastos-analisis/dashboard?fecha_inicio=2023-01-01&fecha_fin=2023-01-31`

**Respuesta exitosa**:

```json
{
  "status": "success",
  "periodo": {
    "fecha_inicio": "2023-01-01",
    "fecha_fin": "2023-01-31"
  },
  "ventas_totales": 1250000.00,
  "gastos_totales": 450000.00,
  "data": {
    "gastos_sobre_ventas": [
      {
        "rubro_id": 1,
        "rubro_nombre": "Alimentos",
        "importe": 125000.00,
        "porcentaje_ventas": 10.00
      },
      {
        "rubro_id": 2,
        "rubro_nombre": "Bebidas",
        "importe": 87500.00,
        "porcentaje_ventas": 7.00
      }
    ],
    "gastos_sobre_total": [
      {
        "rubro_id": 1,
        "rubro_nombre": "Alimentos",
        "importe": 125000.00,
        "porcentaje_gastos_totales": 27.78
      },
      {
        "rubro_id": 2,
        "rubro_nombre": "Bebidas",
        "importe": 87500.00,
        "porcentaje_gastos_totales": 19.44
      }
    ],
    "gastos_relevantes": [
      {
        "rubro_id": 1,
        "rubro_nombre": "Alimentos",
        "importe": 125000.00,
        "porcentaje_gastos_totales": 27.78
      },
      {
        "rubro_id": 2,
        "rubro_nombre": "Bebidas",
        "importe": 87500.00,
        "porcentaje_gastos_totales": 19.44
      },
      {
        "rubro_id": 3,
        "rubro_nombre": "Luz",
        "importe": 65000.00,
        "porcentaje_gastos_totales": 14.44
      },
      {
        "rubro_id": 4,
        "rubro_nombre": "Alquiler",
        "importe": 50000.00,
        "porcentaje_gastos_totales": 11.11
      },
      {
        "rubro_id": 5,
        "rubro_nombre": "Salarios",
        "importe": 47500.00,
        "porcentaje_gastos_totales": 10.56
      }
    ]
  }
}
```

## Códigos de error

- `500` - Error interno del servidor

## Notas

- Si no hay datos disponibles para el período seleccionado, se devolverá un mensaje con estado "warning" y datos vacíos.
- Los gastos relacionados con inversiones (rubro 55) se excluyen de los cálculos.
- Todas las fechas deben proporcionarse en formato ISO: `YYYY-MM-DD`
- Los valores monetarios y porcentajes se redondean a 2 decimales. 