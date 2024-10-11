<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function addEvent(Request $request)
    {
        // Validación de los datos
        $request->validate([
            'titulo' => 'required|string|max:255',
            'image_url' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'descripcion' => 'required|string|max:500'
        ]);

        // Crear nuevo evento
        $evento = new Evento();
        $evento->titulo = $request->input('titulo');
        $evento->descripcion = $request->input('descripcion');

        // Si se sube una imagen, convertirla en base64
        if ($request->hasFile('image_url')) {
            $file = $request->file('image_url');
            $fileContent = base64_encode(file_get_contents($file));
            $evento->image_url = $fileContent; // Guardamos el archivo en formato base64
        }

        // Guardar el evento en la base de datos
        $evento->save();

        // Retornar una respuesta en formato JSON
        return response()->json([
            'message' => 'Evento guardado con éxito',
            'titulo' => $evento->titulo,
            'descripcion' => $evento->descripcion,
            'image_url_base64' => $evento->image_url
        ], 201);
    }

    public function show()
    {
        $eventos = Evento::all();

        return response()->json([
            'message' => 'Eventos encontrados',
            'eventos' => $eventos->map(function($evento) {
                return [
                    'id' => $evento->id,
                    'titulo' => $evento->titulo,
                    'descripcion' => $evento->descripcion,
                    'image_url_base64' => $evento->image_url // Devolvemos la imagen en base64
                ];
            })
        ]);
    }

    public function delete($id)
    {
        $evento = Evento::findOrFail($id);
    
        $evento->delete();
    
        return response()->json([
            'message' => 'Evento eliminado con éxito'
        ], 200);
    }
}
