<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Spatie\Permission\Models\Role;

//Importing laravel-permission models

class UsersController extends Controller
{

    public function __construct() {
        $this->middleware('role:superadmin', ['only' => ['create', 'store', 'edit', 'delete']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::All()->skip(1);
        return view('admin.users',['pagename'=>'Users','users'=>$users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();
        return view('admin.users.create',['pagename'=>'Create User', 'roles'=>$roles]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' =>'required|max:120',
            'email' => 'required|email|unique:users',
            'email' => 'required|max:20|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $request['password'] = bcrypt($request['password']);
        $user = User::create($request->only('email','name','username','password'));

        $roles = $request['roles'];

        if (isset($roles)) {
            foreach ($roles as $role) {
                $role_r = Role::where('id',$role)->firstOrFail();
                $user->assignRole($role_r);
            }
        }


        return redirect()->route('users.index')
            ->with('message',
            'User successfully created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::get();
        return view('admin.users.edit',['pagename' => 'Edit Users','user'=>$user,'roles'=>$roles]);
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
        $user = User::findOrFail($id);

        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:users,email,'.$id,
            'username' => 'required|unique:users,username,'.$id,
            'password' => 'confirmed',
        ]);

        if ($request->get('password') == '') {
            $input = $request->only(['name','email','username']);
        } else {
            $request['password'] = bcrypt($request['password']);
            $input = $request->only(['name','email','username','password']);
        }  

        $user->fill($input)->save();

        $roles = $request['roles'];
        
        if (isset($roles)) {
            $user->roles()->sync($roles);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('users.index')
            ->with('message',
            'User successfully updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')
            ->with('message',
            'User successfully deleted!');
    }
}
