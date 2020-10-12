<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use App\Repositories\ShareRepository;

use Illuminate\Http\Request;
use Storage;

class PassportController extends Controller
{
    public function __construct(ShareRepository $shareRepository)
    {
    $this->shareRepository = $shareRepository;
    }
   /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
 
        $token = $user->createToken('TutsForWeb')->accessToken;
 
        return response()->json(['token' => $token], 200);
    }
 
    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    { 
        //$exist = User::where('username', $request->username)->orWhere('username_legacy', $request->username)->first();
        $exist = User::where('username', $request->username)->orWhere(function($q) use($request) {
                    $q->whereNotNull('username_legacy');
                    $q->where('username_legacy',$request->username);
                })->first();

        if(!$exist) {
            return response()->json([
                'succeswes' => false,
                'message' => 'Usuario no existe'
            ])->setStatusCode(401);
        }

        $credentials = [
            'username' => $exist->username,
            'password' => $request->password
        ];
 
        if ($exist && auth()->attempt($credentials)) {

            if($exist && $exist->is_active == 0) {
                return response()->json([
                    'succeswes' => false,
                    'message' => 'Usuario inactivo'
                ])->setStatusCode(401);
            }
    
            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
            $user = auth()->user();
            $user->roles = auth()->user()->getRoles();
            $newRoles = Role::where('id', auth()->user()->id)->get();
            $person = $exist->group_id !== null ? $this->shareRepository->findByShare($exist->group_id) : null;
            if($person) {
                $user->partnerProfile = $person->partner()->first();
            }
            return response()->json(['token' => $token, 'user' =>  $user, 'userRoles' => $user->roles()->get()], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ])->setStatusCode(401);
        }
    }
 
    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }
}
