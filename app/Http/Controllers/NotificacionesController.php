<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificacionesService;
use Barryvdh\DomPDF\Facade as PDF;

class NotificacionesController extends Controller
{
    public function __construct(NotificacionesService $service)
	{
		$this->service = $service;
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
        $data = $this->service->create($data);
        return $data;
    }
}
