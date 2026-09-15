<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

//Importar el modelo User
use App\Models\User;

class UsuarioController extends Controller
{
    public function index()
    {
        $query = User::query(); // Crear una instancia de la consulta del modelo User
        $users = $query->paginate(10)->withQueryString(); // Paginación de 10 usuarios por página
        return view('admin.user.usuario', compact('users')); // Retornar la vista con los usuarios paginados
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        // Lógica para almacenar un nuevo usuario
    }

    public function show($id)
    {
        // Lógica para mostrar un usuario específico
    }

    public function update($id)
    {
        // Lógica para mostrar el formulario de edición de un usuario
    }

    public function destroy($id)
    {
        // Lógica para eliminar un usuario
    }


}
