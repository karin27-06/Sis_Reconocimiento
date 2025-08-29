<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Empleado\StoreEmployeeRequest;
use App\Http\Requests\Empleado\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Pipelines\FilterByName;
use App\Pipelines\FilterByState;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller{
    public function index(Request $request){
        Gate::authorize('viewAny', Employee::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $query = app(Pipeline::class)
            ->send(Employee::query())
            ->through([
                new FilterByName($search),
                new FilterByState($request->input('state')),
            ])
            ->thenReturn();

        return EmployeeResource::collection($query->paginate($perPage));
    }
    public function store(StoreEmployeeRequest $request){
        Gate::authorize('create', Employee::class);
        $validated = $request->validated();
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('empleados', 'public');
        }
        $employee = Employee::create($validated);
        return response()->json([
            'state' => true,
            'message' => 'Empleado registrado correctamente.',
            'employee' => new EmployeeResource($employee)
        ]);
    }
    public function show(Employee $employee){
        Gate::authorize('view', $employee);
        return response()->json([
            'state' => true,
            'message' => 'Empleado encontrado',
            'employee' => new EmployeeResource($employee)
        ]);
    }
    public function update(UpdateEmployeeRequest $request, Employee $employee){
        Gate::authorize('update', $employee);
        $validated = $request->validated();
        if ($request->hasFile('foto')) {
            // eliminar la foto anterior si existe
            if ($employee->foto && \Storage::disk('public')->exists($employee->foto)) {
                \Storage::disk('public')->delete($employee->foto);
            }
            $validated['foto'] = $request->file('foto')->store('empleados', 'public');
        }
        $employee->update($validated);
        return response()->json([
            'state' => true,
            'message' => 'Empleado actualizado correctamente.',
            'employee' => new EmployeeResource($employee->refresh())
        ]);
    }
    public function destroy(Employee $employee){
        Gate::authorize('delete', $employee);
        // eliminar foto al borrar empleado
        if ($employee->foto && \Storage::disk('public')->exists($employee->foto)) {
            \Storage::disk('public')->delete($employee->foto);
        }
        $employee->delete();
        return response()->json([
            'state'   => true,
            'message' => 'Empleado eliminado correctamente'
        ]);
    }
    #EXPORTACION
    public function exportExcel()
    {
        return Excel::download(new EmployeesExport, 'Empleados.xlsx');
    }
}
