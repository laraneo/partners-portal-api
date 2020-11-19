<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(UserService $userService)
	{
		$this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->userService->index();
        return response()->json([
            'success' => true,
            'data' => $user
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
        $userRequest = $request->all();
        if ($this->userService->checkUser($request['email'])) {
            return response()->json([
                'success' => false,
                'message' => 'Correo ya existe'
            ])->setStatusCode(400);
        }
        return $this->userService->create($userRequest);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->userService->read($id);
        if($user) {
            return response()->json([
                'success' => true,
                'data' => $user
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
        $userRequest = $request->all();
        return $this->userService->update($userRequest, $id);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->userService->delete($id);
        if($user) {
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }
    }

    /**
     * Show login user available
     *
     * @return \Illuminate\Http\Response
     */
    public function checkLogin()
    {
     return $this->userService->checkLogin();
    }

        /**
     * Show login user available
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forcedLogin(Request $request)
    {
        return $this->userService->forcedLogin($request);
    }

        /**
     * Get the specified resource by search.
     *
     * @param  string $term
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request) {
        $data = $this->userService->search($request);
        if($data) {
            return response()->json([
                'success' => true,
                'data' => $data
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
    public function updatePassword(Request $request)
    {
        $userRequest = $request->all();
        return $this->userService->updatePassword($userRequest);
    }

        /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $userRequest = $request->all();
        return $this->userService->forgotPassword($userRequest);
    }
}
