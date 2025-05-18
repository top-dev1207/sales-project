<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturas extends Model
{
    use HasFactory;

    public function latestProv()
    {
        return $this->hasOne(Proveedores::class)->latestOfMany('id');
    }

    public function proveedor_r()
    {
        //Tomo el último registro editado de Proveedor
        $maxId = Proveedores::where('nro_proveedor','=',$this->proveedor)->max('id');       //Obtengo el maximo id de los proveedores $this->proveedor 
 
        return $this->belongsTo(Proveedores::class, 'proveedor','nro_proveedor')        //(clase a relacionar, columna local, columna dest)
            ->where('id','=', $maxId);          //esta es la condiciòn para que mande el último registro      
    }

    public function rubro_r()
    {
        return $this->belongsTo('App\Models\Rubro', 'rubro','valor');      //(clase a relacionar, columna local, columna dest)
    }
    public function condicionIVA_r()
    {
        return $this->belongsTo('App\Models\TipoIVA', 'condicionIVA','valor');      //(clase a relacionar, columna local, columna dest)
    }
    public function estado_remito_r()
    {
        return $this->belongsTo('App\Models\Estado_Documento', 'estado_remito','id');      //(clase a relacionar, columna local, columna dest)
    }
    public function estado_factura_r()
    {
        return $this->belongsTo('App\Models\Estado_Documento', 'estado_factura','id');      //(clase a relacionar, columna local, columna dest)
    }
    public function estadoDocumentoR()
    {
        return $this->belongsTo('App\Models\Estado_Documento', 'estadoDocumento','id');      //(clase a relacionar, columna local, columna dest)
    }
    public function tipoPagoR()
    {
        return $this->belongsTo('App\Models\TipoPago', 'tipoPago','id');      //(clase a relacionar, columna local, columna dest)
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'users_id')->withTrashed();
    }
    public function estado_entrega_r()
    {
        return $this->belongsTo('App\Models\EstadoEntrega', 'estadoEntrega','id');      //(clase a relacionar, columna local, columna dest)
    }
    
    public function categoria()
    {
        return $this->belongsTo('App\Models\CategoriasGasto', 'categoria_id');
    }
}
