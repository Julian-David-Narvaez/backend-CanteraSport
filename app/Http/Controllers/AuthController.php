<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use \stdClass;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    //

        public function register(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'edad' => 'required|integer',
                'tipo_identificacion' => 'required|string|max:20',
                'identificacion' => 'required|integer|unique:users',
                'numero_celular' => 'required|integer',
                'correo' => 'required|email|unique:users|max:255',
                'password' => 'required|string|min:8',
                'id_rol' => 'required|in:admin,profesor,estudiante',
                'id_grupo' => 'nullable|integer',
                'es_menor_de_edad' => 'required|boolean',
                'acudiente' => 'nullable|string|max:100',
                'telefono_acudiente' => 'nullable|integer',
                'correo_acudiente' => 'nullable|email',
                'estado' => 'required|in:activo,inactivo',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'edad' => $request->edad,
                'tipo_identificacion' => $request->tipo_identificacion,
                'identificacion' => $request->identificacion,
                'numero_celular' => $request->numero_celular,
                'correo' => $request->correo,
                'password' => Hash::make($request->password),
                'id_rol' => $request->id_rol,
                'id_grupo' => $request->id_grupo,
                'es_menor_de_edad' => $request->es_menor_de_edad,
                'acudiente' => $request->acudiente,
                'telefono_acudiente' => $request->telefono_acudiente,
                'correo_acudiente' => $request->correo_acudiente,
                'estado' => $request->estado
            ]);
            $token = $user->createToken('authToken')->plainTextToken;

            return response()
                    ->json(['data' => $user, 'acess_token' => $token, 'token_tupe' => 'Bearer',]);
        }
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('correo', 'password')))
        {
            return response()
            ->json(['message' => 'Unauthorized'], 401);
        }

        $user = user::where('correo', $request['correo'])->firstOrfail();

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'message' => 'Hi ' .$user->nombre,
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'role' => $user->id_rol,
            ]);
    }
    public function update(Request $request, $id)
{
    //if (Auth::user()->id_rol !== 'admin') {
      //  return response()->json(['error' => 'Unauthorized'], 403);
   // }
    $user = User::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'nombre' => 'sometimes|string|max:100',
        'apellido' => 'sometimes|string|max:100',
        'edad' => 'sometimes|integer',
        'tipo_identificacion' => 'sometimes|string|max:20',
        'identificacion' => 'sometimes|integer|unique:users,identificacion,'.$user->id,
        'numero_celular' => 'sometimes|integer',
        'correo' => 'sometimes|email|unique:users,correo,'.$user->id.'|max:255',
        'password' => 'sometimes|string|min:8',
        'id_rol' => 'sometimes|in:admin,profesor,estudiante',
        'id_grupo' => 'nullable|integer',
        'es_menor_de_edad' => 'sometimes|boolean',
        'acudiente' => 'nullable|string|max:100',
        'telefono_acudiente' => 'nullable|integer',
        'correo_acudiente' => 'nullable|email',
        'estado' => 'sometimes|in:activo,inactivo',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $user->update([
        'nombre' => $request->nombre ?? $user->nombre,
        'apellido' => $request->apellido ?? $user->apellido,
        'edad' => $request->edad ?? $user->edad,
        'tipo_identificacion' => $request->tipo_identificacion ?? $user->tipo_identificacion,
        'identificacion' => $request->identificacion ?? $user->identificacion,
        'numero_celular' => $request->numero_celular ?? $user->numero_celular,
        'correo' => $request->correo ?? $user->correo,
        'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
        'id_rol' => $request->id_rol ?? $user->id_rol,
        'id_grupo' => $request->id_grupo ?? $user->id_grupo,
        'es_menor_de_edad' => $request->es_menor_de_edad ?? $user->es_menor_de_edad,
        'acudiente' => $request->acudiente ?? $user->acudiente,
        'telefono_acudiente' => $request->telefono_acudiente ?? $user->telefono_acudiente,
        'correo_acudiente' => $request->correo_acudiente ?? $user->correo_acudiente,
        'estado' => $request->estado ?? $user->estado
    ]);

    return response()->json(['message' => 'User updated successfully', 'user' => $user]);
}
public function index(Request $request)
{
    {
        $query = User::query();

        if ($request->has('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        
        if ($request->has('search')) {
            $search = $request->input('search');

            
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido', 'like', '%' . $search . '%')
                    ->orWhere('tipo_identificacion', 'like', '%' . $search . '%')
                    ->orWhere('identificacion', 'like', '%' . $search . '%')
                    ->orWhere('id_rol', 'like', '%' . $search . '%');
            });
        }

        // Obtener los usuarios filtrados
        $users = $query->get();

        return response()->json($users);
    }
}
public function show($id)
{
    $user = User::find($id);

    if ($user) {
        return response()->json($user);
    } else {
        return response()->json(['message' => 'User not found'], 404);
    }
}
public function token()
{
    // Obtener el usuario autenticado
    $user = auth()->user();

    if ($user) {
        return response()->json($user);
    } else {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}

}

