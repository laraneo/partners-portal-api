<?php

namespace App\Http\Controllers;

use App\BackOffice\Services\ClientesService;

use Illuminate\Http\Request;

class ClientesController extends Controller
{
    public function __construct(ClientesService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        $data = $this->service->index($request->query('perPage'));
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  string nit
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findByNit(Request $request)
    {
        $shareLogin = auth()->user()->group_id;
        $data = $this->service->findByNit($shareLogin);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
