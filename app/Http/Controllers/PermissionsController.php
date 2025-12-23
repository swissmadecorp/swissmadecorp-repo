<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class PermissionsController extends Controller
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
        $permissions = Permission::all();

        return view('admin.permissions',['pagename'=>'Permissions'])->with('permissions',$permissions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();

        return view('admin.permissions.create',['pagename'=>'Add Permission'])->with('roles',$roles);
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
            'name' => 'required|max:40',
        ]);


        $name = $request['name'];
        $permission = new Permission();
        $permission->name = $name;

        $roles = $request['roles'];
        $permission->save();

        if (!empty($request['roles'])) {
            foreach ($roles as $role) {
                $r = Role:: where('id',$role)->firstOrFail();

                $permission = Permission::where('name','$name')->first();
                $r->givePermissionTo($permission);
            }
        }

        return redirect()->route('permissions.index')
            ->with('message',
             'Permission '. $permission->name.' updated!');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect('permissions');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        return view('admin.permissions.edit', ['pagename'=>'Edit Permission'], compact('permission'));
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
        $permission = Permission::findOrFail($id);
        $this->validate($request, [
            'name' => 'required|max:40',
        ]);

        $input = $request->all();
        $permission->fill($input)->save();

        return redirect()
                ->route('permissions.index')
                ->with('message', "'".$permission->name."' updated!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        if ($permission->name == 'Administer roles & permissions') {
            return redirect()
                ->route('permissions.index')
                ->with('message', 'Cannot delete this Permission!');

        }

        $permission->delete();

        return redirect()
                ->route('permissions.index')
                ->with('message', 'Permission deleted!');

    }
}
