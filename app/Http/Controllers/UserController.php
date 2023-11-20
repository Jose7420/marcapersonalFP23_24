<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Mostrar información de un usuario.
     * @param  int  $id
     * @return Response
     */
    public function showProfile($nombre,$apellidos = 'sin apellido')
    {
        //$user = User::findOrFail($id);
        $user = [
            'nombre'=> $nombre,
            'apellidos' => $apellidos
        ];
        return view('user.profile',array('usuario' => $user)) ;
    }
}
