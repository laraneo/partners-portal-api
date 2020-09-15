<?php

namespace App\Http\Controllers;

use App\BackOffice\Services\ReportePagosService;

use Illuminate\Http\Request;

class ReportePagosController extends Controller
{
    public function __construct(ReportePagosService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(auth()->user()->hasRole('socio')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos'
            ])->setStatusCode(400);
        }
        $data = $this->service->index($request->query('perPage'));
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data = $this->service->create($data, $request);
        return $data;
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->service->read($id);
        if($response) {
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        }
    }

        /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(auth()->user()->hasRole('socio')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos'
            ])->setStatusCode(400);
        }
        $body = $request->all();
        $response = $this->service->update($body, $id);
        if($response) {
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        }
    }

    /**
     * Get the specified resource by search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request) {
        if(auth()->user()->hasRole('socio')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos'
            ])->setStatusCode(400);
        }
        $data = $this->service->filter($request);
        if($data) {
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }
    }

    public function findByLogin() {
     $data =  $this->service->findByLogin();
     return response()->json([
        'success' => true,
        'data' => $data,
      ]);;
    }
}
