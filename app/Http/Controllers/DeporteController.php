<?php

namespace App\Http\Controllers;

use App\Models\deporte;
use Illuminate\Http\Request;

class DeporteController extends Controller
{

    public function addSport(Request $request)
    {
        // Validar que se reciba solo una URL de imagen
        $request->validate([
            'image_url' => 'required|url',
            'nombre' => 'required|string|max:255'
        ]);
    
        // Guardar la URL de la imagen y el nombre en la base de datos
        $deporte = new Deporte(); // Usar el modelo Deporte
        $deporte->nombre = $request->input('nombre');  // Guardar el nombre
        $deporte->image_url = $request->input('image_url');  // Guardar la URL de la imagen
        $deporte->save();
    
        return response()->json([
            'message' => 'Imagen y nombre guardados con éxito',
            
            'nombre' => $deporte->nombre,
            'image_url' => $deporte->image_url
        ], 201);
    }
    
    public function show()
    {
        $deportes = Deporte::all();
    
        return response()->json([
            'message' => 'Deportes encontrados',
            'deportes' => $deportes->map(function($deporte) {
                return [
                    'id' => $deporte->id,
                    'nombre' => $deporte->nombre,
                    'image_url' => $deporte->image_url
                ];
            })
        ]);
    }
    
    public function delete($id)
    {
        // Buscar el deporte por su ID
        $deporte = Deporte::findOrFail($id);
    
        // Eliminar el registro de la base de datos (no es necesario eliminar un archivo físico)
        $deporte->delete();
    
        return response()->json([
            'message' => 'Deporte eliminado con éxito'
        ], 200);
    }

}
