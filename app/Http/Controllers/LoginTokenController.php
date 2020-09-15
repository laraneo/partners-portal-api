<?php

namespace App\Http\Controllers;

use App\BackOffice\Services\LoginTokenService;

use Illuminate\Http\Request;

class LoginTokenController extends Controller
{
    public function __construct(LoginTokenService $service)
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function find()
    {
        $response = $this->service->find();
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }
}
