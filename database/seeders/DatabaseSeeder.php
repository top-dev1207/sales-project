<?php
namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AreasSeeder::class);
        $this->call(ClimasSeeder::class);
        $this->call(EstadoCargaSeeder::class);
        $this->call(EstadoDocumentoSeeder::class);
        $this->call(EstadoEntregaSeeder::class);
        $this->call(EstadoEmpleadoSeeder::class);
        $this->call(TipoIVASeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(TurnosSeeder::class);
        $this->call(PuestosSeeder::class);  
        
        $this->call(MovimientosSeeder::class);
        $this->call(ProveedoresSeeder::class);
        $this->call(GrupoCajasSeeder::class);
        $this->call(RubroSeeder::class);
        $this->call(ArticuloVentaSeeder::class);        //Parametrizable según negocio

        $this->call(FormaCobroTemple1Seeder::class);    // Se usa en Proveedores.   Ver de "comunizar"
        $this->call(FormaCobroCervelarSeeder::class);     // --> se usa en Ventas graficos y resultados

        $this->call(CajasSeeder::class);             //cuentas de cada negocio
        
        $this->call(UsersSeeder::class);        //reemplaza a UserAndNotesSeeder
        $this->call(cc01_RolesPermisos::class);  
        $this->call(cc02_PermisosTransfSeeder::class);  
        $this->call(cc03_AddPermisosValidacionN2::class);  

    }                               
}                                   
