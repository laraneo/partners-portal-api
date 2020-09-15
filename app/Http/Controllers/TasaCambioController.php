<?php

namespace App\Http\Controllers;

use App\BackOffice\Services\TasaCambioService;
use App\Services\SoapService;

use Illuminate\Http\Request;

class TasaCambioController extends Controller
{
    public function __construct(
        TasaCambioService $service,
        SoapService $soapService
        )
    {
        $this->service = $service;
        $this->soapService = $soapService;
    }
  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->service->index();
        if(!$data) {
            $data = $this->soapService->getTasaDelDia();
        }
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
