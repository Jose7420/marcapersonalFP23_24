<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function getEdit($id)
    {
        $id = $id > 10 ? 10: $id;
        return view('catalog.edit', ['id' => $id]);
    }
    public function getShow($id)
    {
        $id = $id > 10 ? 10: $id;
        return view('catalog.show', ['id' => $id]);
    }
}
