<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventarioContoller extends Controller
{
    public function index()
    {
        return view('admin.productos.inventario');
    }
}
