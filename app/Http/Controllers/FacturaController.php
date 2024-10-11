<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function addFactura(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:500',
            'comprobante_pago' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048' // Acepta PDF e imágenes
        ]);

        // Convertir el archivo en base64
        $file = $request->file('comprobante_pago');
        $fileContent = base64_encode(file_get_contents($file));

        // Crear una nueva factura
        $factura = new Factura();
        $factura->titulo = $request->input('titulo');
        $factura->descripcion = $request->input('descripcion');
        $factura->comprobante_pago = $fileContent; // Guardamos el archivo en base64
        $factura->save();
    
        return response()->json([
            'message' => 'Factura y comprobante guardados con éxito',
            'titulo' => $factura->titulo,
            'descripcion' => $factura->descripcion,
            'comprobante_pago_base64' => $fileContent 
        ], 201);
    }

    public function show()
    {
        $facturas = Factura::all();
    
        return response()->json([
            'message' => 'Facturas encontradas',
            'facturas' => $facturas->map(function($factura) {
                return [
                    'id' => $factura->id,
                    'titulo' => $factura->titulo,
                    'descripcion' => $factura->descripcion,
                    'comprobante_pago_base64' => $factura->comprobante_pago 
                ];
            })
        ]);
    }
    
    public function delete($id)
    {
        $factura = Factura::findOrFail($id);
    
        $factura->delete();
    
        return response()->json([
            'message' => 'Factura y comprobante eliminados con éxito'
        ], 200);
    }

}
