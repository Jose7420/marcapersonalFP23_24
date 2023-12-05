<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{

    public function getIndex(){
        return view('estudiantes.index',['arrayEstudiantes' => Estudiante::all()]);
    }

    public function getShow($id)
    {
        return view('estudiantes.show')
            ->with('estudiante', Estudiante::findOrFail($id));
    }

    public function getEdit($id) {
        return view('estudiantes.edit')
            ->with("estudiante", Estudiante::findOrFail($id));
    }

    public function putEdit(Request $resquest, $id) {

        $estudiante = Estudiante::findOrFail($id);
        $estudiante->update($resquest->all());

        // $estudiante->nombre =  $resquest->nombre;
        // $estudiante->apellidos =  $resquest->apellidos;
        // $estudiante->votos =  $resquest->votos;
        // $estudiante->ciclo = $resquest->ciclo;
        // $estudiante->save();

        return redirect(action([self::class, 'getShow'],['id'=> $estudiante->id]));

    }

    public function getCreate(){
        return view('estudiantes.create');
    }

    public function store(Request $resquest) {

       $estudiante = Estudiante::create($resquest->all());

       return redirect(action([self::class, 'getShow'],['id'=> $estudiante->id]));

    }
}
