<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use App\Models\User;
use App\Models\recordatorio_usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarioController extends Controller
{
    public function create(Request $request)
    {
        // Validación de los datos
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_hora' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // Obtener el id del usuario autenticado
        $asignador_id = Auth::id();
        $usuario = User::find($asignador_id);

        // Verificar si el usuario tiene rol de profesor o admin
        if ($usuario->id_rol !== 'profesor' && $usuario->id_rol !== 'admin') {
            return response()->json(['message' => 'No tienes permisos para crear eventos.'], 403);
        }

        // Guardar el evento
        $calendario = new Calendario();
        $calendario->titulo = $validatedData['titulo'];
        $calendario->descripcion = $validatedData['descripcion'];
        $calendario->fecha_hora = $validatedData['fecha_hora'];
        $calendario->save();

        // Obtener todos los usuarios con rol de estudiante, profesor y admin
        $usuariosEstudiantes = User::where('id_rol', 'estudiante')->get();
        $usuariosProfesores = User::where('id_rol', 'profesor')->get();
        $usuariosAdmins = User::where('id_rol', 'admin')->get();

        // Insertar a todos los estudiantes en la tabla de relaciones
        foreach ($usuariosEstudiantes as $usuarioEstudiante) {
            recordatorio_usuario::create([
                'asignador_id' => $asignador_id,
                'recordatorio_id' => $calendario->id,
                'usuario_id' => $usuarioEstudiante->id,
            ]);
        }

        // Si el creador es un profesor, también agregar a los administradores
        if ($usuario->id_rol === 'profesor') {
            foreach ($usuariosAdmins as $usuarioAdmin) {
                recordatorio_usuario::create([
                    'asignador_id' => $asignador_id,
                    'recordatorio_id' => $calendario->id,
                    'usuario_id' => $usuarioAdmin->id,
                ]);
            }
        }

        // Si el creador es un administrador, también agregar a los profesores
        if ($usuario->id_rol === 'admin') {
            foreach ($usuariosProfesores as $usuarioProfesor) {
                recordatorio_usuario::create([
                    'asignador_id' => $asignador_id,
                    'recordatorio_id' => $calendario->id,
                    'usuario_id' => $usuarioProfesor->id,
                ]);
            }
        }

        // Agregar el usuario (profesor o admin) que creó el evento
        recordatorio_usuario::create([
            'asignador_id' => $asignador_id,
            'recordatorio_id' => $calendario->id,
            'usuario_id' => $asignador_id, // El creador
        ]);

        return response()->json(['message' => 'Evento agregado con éxito'], 201);
    }

    public function update(Request $request, $id)
    {
        // Validación de los datos
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_hora' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // Obtener el id del usuario autenticado
        $asignador_id = Auth::id();
        $usuario = User::find($asignador_id);

        // Verificar si el usuario tiene rol de profesor o admin
        if ($usuario->id_rol !== 'profesor' && $usuario->id_rol !== 'admin') {
            return response()->json(['message' => 'No tienes permisos para editar eventos.'], 403);
        }

        // Encontrar el evento por su ID
        $calendario = Calendario::findOrFail($id);

        // Actualizar los campos
        $calendario->titulo = $validatedData['titulo'];
        $calendario->descripcion = $validatedData['descripcion'];
        $calendario->fecha_hora = $validatedData['fecha_hora'];
        $calendario->save();

        // Eliminar relaciones antiguas
        recordatorio_usuario::where('recordatorio_id', $calendario->id)->delete();

        // Obtener todos los usuarios por roles
        $usuariosEstudiantes = User::where('id_rol', 'estudiante')->get();
        $usuariosProfesores = User::where('id_rol', 'profesor')->get();
        $usuariosAdmins = User::where('id_rol', 'admin')->get();

        // Insertar a todos los estudiantes en la tabla de relaciones
        foreach ($usuariosEstudiantes as $usuarioEstudiante) {
            recordatorio_usuario::create([
                'asignador_id' => $asignador_id,
                'recordatorio_id' => $calendario->id,
                'usuario_id' => $usuarioEstudiante->id,
            ]);
        }

        // Si el actualizador es un profesor, también agregar a los administradores
        if ($usuario->id_rol === 'profesor') {
            foreach ($usuariosAdmins as $usuarioAdmin) {
                recordatorio_usuario::create([
                    'asignador_id' => $asignador_id,
                    'recordatorio_id' => $calendario->id,
                    'usuario_id' => $usuarioAdmin->id,
                ]);
            }
        }

        // Si el actualizador es un admin, también agregar a los profesores
        if ($usuario->id_rol === 'admin') {
            foreach ($usuariosProfesores as $usuarioProfesor) {
                recordatorio_usuario::create([
                    'asignador_id' => $asignador_id,
                    'recordatorio_id' => $calendario->id,
                    'usuario_id' => $usuarioProfesor->id,
                ]);
            }
        }

        // Agregar el usuario (profesor o admin) que actualizó el evento
        recordatorio_usuario::create([
            'asignador_id' => $asignador_id,
            'recordatorio_id' => $calendario->id,
            'usuario_id' => $asignador_id, // El actualizador
        ]);

        return response()->json(['message' => 'Evento actualizado con éxito'], 200);
    }

    public function destroy($id)
    {
        // Obtener el id del usuario autenticado
        $asignador_id = Auth::id();
        $usuario = User::find($asignador_id);

        // Verificar si el usuario tiene rol de profesor o admin
        if ($usuario->id_rol !== 'profesor' && $usuario->id_rol !== 'admin') {
            return response()->json(['message' => 'No tienes permisos para eliminar eventos.'], 403);
        }

        // Encontrar el evento por su ID
        $calendario = Calendario::findOrFail($id);

        // Eliminar las relaciones en recordatorio_usuario
        recordatorio_usuario::where('recordatorio_id', $calendario->id)->delete();

        // Eliminar el evento
        $calendario->delete();

        // Respuesta JSON indicando éxito
        return response()->json(['message' => 'Evento eliminado con éxito'], 200);
    }

    public function show()
    {
        // Eliminar eventos expirados antes de obtener todos los eventos
        $this->eliminarEventosExpirados();

        // Obtener todos los eventos
        $calendarios = Calendario::all();

        // Respuesta JSON con todos los eventos
        return response()->json($calendarios, 200);
    }

    private function eliminarEventosExpirados()
    {
        // Obtén la fecha y hora actuales
        $fechaActual = Carbon::now('America/Bogota');

        // Elimina las relaciones de recordatorio_usuarios que son anteriores a la fecha actual
        recordatorio_usuario::whereHas('calendario', function($query) use ($fechaActual) {
            $query->where('fecha_hora', '<', $fechaActual);
        })->delete();

        // Luego, elimina los eventos que son anteriores a la fecha actual
        Calendario::where('fecha_hora', '<', $fechaActual)->delete();
    }

}
