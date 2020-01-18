<?php


namespace App\Http\Controllers;


use App\Http\Middleware\CheckRole;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller {

	public function __construct() {
		$this->middleware(CheckRole::class);
	}

	public function index() {
		$menus = config('menus.admin.child');
		return view('admin.admin',[ 'menus' => $menus[__FUNCTION__]]);
	}

	public function userList() {
		$users = User::all();
		$userList = [];
		foreach ($users as $user) {
			$userList[] = [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'role' => $user->role,
			];
		}
		return view('admin.userlist',['users' => $userList]);
	}

	public function getUserEdit($id = '') {
			$user = User::find($id);
			if(!$user) {
				return redirect(null,404);
			}
			return view('admin.editUser',[
				'id' => $user->id,
				'email' => $user->email,
				'name' => $user->name,
				'currentRole' => $user->role,
				'roles' => config('app.roles'),
			]);
	}

	public function postUserEdit(Request $request, $id = '') {
		/** @var User $user */
		$user = User::find($id);
		if(!$user) {
			return redirect(null,404);
		}
		$user->name = $request->name;
		$user->email = $request->email;
		$user->role = $request->role;
		if($request->password !== null) {
			$user->password = Hash::make($request->password);
			$user->setRememberToken(Str::random(60));
		}
		$user->save();
		return redirect()->action('AdminController@userList');
	}
}