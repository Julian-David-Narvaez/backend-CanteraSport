<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UsersExport
{
    public function export(Request $request)
    {
        // Crear consulta inicial sin filtros
        $query = User::query();

        // Aplicar filtros solo si existen en la solicitud
        if ($request->has('search') && $request->input('search') != '') {
            $search = $request->input('search');

            // Filtro para varios campos
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido', 'like', '%' . $search . '%')
                    ->orWhere('identificacion', 'like', '%' . $search . '%')
                    ->orWhere('id_rol', 'like', '%' . $search . '%');
            });
        }

        // Aplicar filtro por estado si existe en la solicitud
        if ($request->has('estado') && $request->input('estado') != '') {
            $query->where('estado', $request->input('estado'));
        }

        // Obtener los usuarios filtrados (o todos si no hay filtros)
        $users = $query->get();

        // Crear archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Definir encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Apellido');
        $sheet->setCellValue('D1', 'Correo');
        $sheet->setCellValue('E1', 'Edad');
        $sheet->setCellValue('F1', 'Tipo Identificación');
        $sheet->setCellValue('G1', 'Identificación');
        $sheet->setCellValue('H1', 'Número Celular');
        $sheet->setCellValue('I1', 'Rol');
        $sheet->setCellValue('J1', 'Estado');
        $sheet->setCellValue('L1', 'Nombre Acudiente');
        $sheet->setCellValue('K1', 'Correo Acudiente');
        $sheet->setCellValue('M1', 'Telefono del Acudiente');

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);

        


        // Rellenar los datos filtrados o todos los usuarios
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->id);
            $sheet->setCellValue('B' . $row, $user->nombre);
            $sheet->setCellValue('C' . $row, $user->apellido);
            $sheet->setCellValue('D' . $row, $user->correo);
            $sheet->setCellValue('E' . $row, $user->edad);
            $sheet->setCellValue('F' . $row, $user->tipo_identificacion);
            $sheet->setCellValue('G' . $row, $user->identificacion);
            $sheet->setCellValue('H' . $row, $user->numero_celular);
            $sheet->setCellValue('I' . $row, $user->id_rol);
            $sheet->setCellValue('J' . $row, $user->estado);
            $sheet->setCellValue('K' . $row, $user->acudiente);
            $sheet->setCellValue('L' . $row, $user->correo_acudiente);
            $sheet->setCellValue('M' . $row, $user->telefono_acudiente);

            $row++;
        }

        // Crear el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'usuarios.xlsx';

        // Guardar el archivo en la carpeta temporal
        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFilePath);

        // Retornar el archivo como descarga
        return response()->download($tempFilePath, $filename)->deleteFileAfterSend(true);
    }
}
