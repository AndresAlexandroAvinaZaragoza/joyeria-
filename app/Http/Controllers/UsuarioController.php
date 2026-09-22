<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

//Importar el modelo User
use App\Models\Rol;
use App\Models\User;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuariosQuery = User::with('rol');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $usuariosQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('rol', function ($roleQuery) use ($search) {
                        $roleQuery->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('rol_id')) {
            $usuariosQuery->where('rol_id', $request->input('rol_id'));
        }

        $usuarios = $usuariosQuery->paginate(5, ['*'], 'page')->withQueryString();
        $roles = Rol::orderBy('nombre')->get();

        return view('admin.user.usuario', compact('usuarios', 'roles'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => ['required', 'exists:rol,id_rol'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
        ]);

        return redirect()->route('usuarios.index')->with('status', 'Usuario creado correctamente.');
    }


    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'rol_id' => ['required', 'exists:rol,id_rol'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->rol_id = $request->rol_id;
        $user->save();

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('usuarios.index')->with('status', 'Usuario eliminado correctamente.');
    }


}
