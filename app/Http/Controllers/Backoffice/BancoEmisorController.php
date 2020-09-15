<?php

namespace App\Http\Controllers;

use App\BackOffice\Services\BancoEmisorService;

use Illuminate\Http\Request;

class BancoEmisorController extends Controller
{
    public function __construct(BancoEmisorService $service)
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
