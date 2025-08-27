<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        #User
        Permission::create(['name' => 'crear usuarios']);
        Permission::create(['name' => 'editar usuarios']);
        Permission::create(['name' => 'eliminar usuarios']);
        Permission::create(['name' => 'ver usuarios']);
        # Roles
        Permission::create(['name' =>'crear roles']);
        Permission::create(['name' =>'editar roles']);
        Permission::create(['name' =>'eliminar roles']);
        Permission::create(['name' =>'ver roles']);
        # Permisos
        Permission::create(['name' =>'crear permisos']);
        Permission::create(['name' =>'editar permisos']);
        Permission::create(['name' =>'eliminar permisos']);
        Permission::create(['name' =>'ver permisos']);
        #Presentaciones
        Permission::create(['name' => 'crear presentaciones']);
        Permission::create(['name' => 'editar presentaciones']);
        Permission::create(['name' => 'eliminar presentaciones']);
        Permission::create(['name' => 'ver presentaciones']);
        #empleado
        Permission::create(['name' => 'crear empleados']);
        Permission::create(['name' => 'editar empleados']);
        Permission::create(['name' => 'eliminar empleados']);
        Permission::create(['name' => 'ver empleados']);
        #Tipo Empleados
        Permission::create(['name' => 'crear tipos_empleados']);
        Permission::create(['name' => 'editar tipos_empleados']);
        Permission::create(['name' => 'eliminar tipos_empleados']);
        Permission::create(['name' => 'ver tipos_empleados']);
    }
}
