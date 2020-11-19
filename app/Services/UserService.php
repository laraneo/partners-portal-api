<?php

namespace App\Services;

use App\Role;
use App\User;

use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
use App\Repositories\ShareRepository;
use Illuminate\Http\Request;
use App\BackOffice\Services\LoginTokenService;

use Storage;


class UserService {

		public function __construct(
			UserRepository $repository,
			LoginTokenService $loginTokenService,
			ShareRepository $shareRepository,
			User $userModel
			) {
			$this->repository = $repository;
			$this->loginTokenService = $loginTokenService;
			$this->shareRepository = $shareRepository;
			$this->userModel = $userModel;
		}

		public function index() {
			return $this->repository->all();
		}
		
		public function create($request) {
			if ($this->repository->checkUsernameLegacy($request)) {
				return response()->json([
					'success' => false,
					'message' => 'El usuario legacy ya existe'
				])->setStatusCode(400);
			}

			if ($this->repository->check($request)) {
				return response()->json([
					'success' => false,
					'message' => 'El usuario ya existe'
				])->setStatusCode(400);
			}
			$data = $this->repository->create($request);
			if ($data) {
				return response()->json([
					'success' => true,
					'data' => $data
				])->setStatusCode(200);
			}
		}

		public function update($request, $id) {

			if ($this->repository->checkFieldBeforeUpdate('username_legacy' ,$request['username_legacy'], $id)) {
				return response()->json([
					'success' => false,
					'message' => 'El usuario legacy ya existe'
				])->setStatusCode(400);
			}


			// if ($this->repository->checkFieldBeforeUpdate('email' ,$request['email'], $id)) {
			// 	return response()->json([
			// 		'success' => false,
			// 		'message' => 'El correo ya existe'
			// 	])->setStatusCode(400);
			// }


			$user = $this->repository->find($id);
			$user->revokeAllRoles();
			$user = $this->repository->find($id);
			$roles = json_decode($request['roles']);
			
			if(count($roles)) {
				foreach ($roles as $role) {
					$user->assignRole($role);
				}
			}
			$data = $this->repository->update($id, $request);
			if($data) {
				return response()->json([
					'success' => true,
					'data' => $data
				]);
			}
		}

		public function read($id) {
						return $this->repository->find($id);
		}

		public function delete($id) {
							return $this->repository->delete($id);
		}

		public function checkUser($user) {
			return $this->repository->checkUser($user);
		}

		public function checkLogin() {
			if (Auth::check()) {
				$user = auth()->user();
				$user->roles = auth()->user()->getRoles();
				$newRoles = Role::where('id', auth()->user()->id)->get();
				$person = $this->shareRepository->findByShare($user->username);
				if($person) {
					$user->partnerProfile = $person->partner()->first();
				}
				return response()->json([
					'success' => true,
					'user' => $user,
					'userRoles' => $user->roles()->get()
				]);
			}
			return response()->json([
                'success' => false,
                'message' => 'You must login first'
            ])->setStatusCode(401);
		}

		public function forcedLogin($request) {
			$user =  $this->repository->forcedLogin($request['socio']);
			if($user) {
				$token = $this->loginTokenService->find($request['socio'], $request['token']);
				if($token) {
					$auth = Auth::login($user);
					$token = auth()->user()->createToken('TutsForWeb')->accessToken;
					$user = auth()->user();
					$user->roles = auth()->user()->getRoles();
					$newRoles = $user->roles()->get();
					return response()->json(['token' => $token, 'user' =>  $user, 'userRoles' => $newRoles], 200);
					}
				}
		return response()->json([
			'success' => false,
			'message' => 'You must login first'
		])->setStatusCode(401);
		}

		public function search($query) {
			return $this->repository->search($query);
		}

		public function updatePassword($request) {

			$user = auth()->user();
			$check = \Hash::check($request['prevPassword'],$user->password);
			
			if(!$check) {
				return response()->json([
					'success' => false,
					'message' => 'Los datos de usuario no coinciden, intente de nuevo'
				])->setStatusCode(400);
			}

			$attr = [ 'password' => $request['password'] ];
			$data = $this->repository->update($user->id, $attr);
			return response()->json([
				'success' => true,
				'data' => $data
			]);

		}

		public function forgotPassword($request) {
			$isValid = $this->userModel->query()
			->where('doc_id', $request['doc_id'])
			->where('group_id', $request['group_id'])
			->where('email', $request['email'])->first();

			if($isValid) {
				$attr = [ 'password' => $request['password'] ];
				$this->repository->update($isValid->id, $attr);
				return response()->json([
					'success' => true,
					'data' => 'Clave exitosa'
				]);
			}
			return response()->json([
				'success' => false,
				'message' => 'Los datos de usuario no coinciden, intente de nuevo'
			])->setStatusCode(400);
		}
}