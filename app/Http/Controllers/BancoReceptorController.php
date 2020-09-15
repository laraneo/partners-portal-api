<?php

namespace App\Http\Controllers;

use App\BackOffice\Services\BancoReceptorService;

use Illuminate\Http\Request;

class BancoReceptorController extends Controller
{
    public function __construct(BancoReceptorService $service)
    {
        $this->service = $service;
    }
  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = $this->service->index();
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }
}
