<?php

namespace App\Http\Controllers\API;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProyectoResource;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use App\Models\User;
use App\Providers\GitHubServiceProvider;

class ProyectoController extends Controller
{

    public $modelclass = Proyecto::class;
    protected $githubService;

    public function __construct(GitHubServiceProvider $githubService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Proyecto::class, 'proyecto');
        $this->githubService = $githubService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campos = ['nombre', 'dominio'];
        $otrosFiltros = ['docente_id'];
        $query = FilterHelper::applyFilter($request, $campos, $otrosFiltros);
        $request->attributes->set('total_count', $query->count());
        $queryOrdered = FilterHelper::applyOrder($query, $request);
        return ProyectoResource::collection($queryOrdered->paginate($request->perPage));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {

        $proyecto = json_decode($request->getContent(), true);

        $proyecto['docente_id'] = auth()->id();

        $proyecto = Proyecto::create($proyecto);

        return new ProyectoResource($proyecto);
    }


    /**
     * Display the specified resource.
     */
    public function show(Proyecto $proyecto)
    {
        return new ProyectoResource($proyecto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proyecto $proyecto)
    {
        $proyectoData = $request->all();
        if ($proyectoRepoZip = $request->file('fichero')) {
            $request->validate([
                'fichero' => 'required|mimes:zip,rar,bz,bz2,7z|max:5120', // Se permiten ficheros comprimidos de hasta 5 MB
            ], [
                'fichero.required' => 'Por favor, selecciona un fichero.',
                'fichero.mimes' => 'El fichero debe ser un fichero ZIP.',
                'fichero.max' => 'El tamaño del fichero no debe ser mayor a 5 MB.',
            ]);

            $path = $proyectoRepoZip->store('repoZips', ['disk' => 'public']);
            $proyectoData['fichero'] = $path;
        } else {
            $proyectoData['fichero'] = $proyecto->fichero;
        }

        if (isset($path) && strlen($proyecto->url_github) == 0) {
            $githubResponse = $this->githubService->createRepo($proyecto);

            if ($githubResponse->getStatusCode() === 200) {
                $jsonResponse = json_decode($githubResponse->getBody(), true);
                $proyectoData['url_github'] = $jsonResponse['html_url'];
            }
        } else {
            $proyecto->update($proyectoData);
        }

        $proyecto->update($proyectoData);
        if (isset($path) && $proyecto->urlPerteneceOrganizacion()) {
            $this->githubService->pushZipFiles($proyecto);
        } else if (strlen($proyectoData['url_github']) > 0) {
            $proyectoData['fichero'] = $this->githubService->getZipFileFromRepo($proyectoData['url_github']);
            $proyecto->update($proyectoData);
            $this->githubService->pushZipFilesCommon($proyecto);
        } else {
            $proyecto->update($proyectoData);
        }

        // $this->githubService->deleteRepo($proyecto);

        return new ProyectoResource($proyecto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proyecto $proyecto)
    {
        $proyecto->delete();
    }
}
