<?php

namespace App\Http\Controllers;
use App\Exports\UsersExport;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function export(Request $request)
    {
        $export = new UsersExport( );
        return $export->export($request);
    }
}
