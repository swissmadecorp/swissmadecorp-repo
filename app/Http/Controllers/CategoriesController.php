<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Session;

class CategoriesController extends Controller
{

    public function __construct() {
        $this->middleware('role:superadmin|administrator', ['only' => ['create', 'store', 'edit', 'delete']]);
        // Alternativly
        //$this->middleware('role:viewer', ['except' => ['index', 'show']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest()->get();
        return view ('admin.categories', ['pagename' => 'Category Page', 'categories'=>$categories]);
 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('admin.categories.create', ['pagename' => 'New Category']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());
        //
        
        //$category = new Categories;
        //$category->category_name = $request['category_name'];
        //$category->save();

        $validator = \Validator::make($request->all(),[
            'category_name' => 'required|unique:categories'
        ]);

        if ($validator->fails()){
            return redirect('admin/categories/create')
                ->withErrors($validator)
                ->withInput();
        } else {
            
            $data = [
                'category_name' => $request['category_name'],
                'category_location' => $request['category_location'],
            ];
            
            if (isset($request['image'])) {
                $data['image'] = $request['image'];
            }

            $category = Category::create($data);
            return redirect("admin/categories");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //$category = Categories::find($id);
        //return view ('categories', array('category' => $category));
 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::find($id);
        if (!$category)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        return view('admin.categories.edit',['pagename' => 'Edit Category', 'category' => $category]);
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

        $validator = \Validator::make($request->all(),[
            'category_name' => 'required|min:2'
        ]);

        if ($validator->fails()){
            return redirect('admin/categories/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        } else {
            $this->updateCategory($request,$id);
            // redirect
            Session::flash('message', 'Successfully updated category!');
            return redirect('admin/categories');
        }

    }

    public static function updateCategory($request,$id) {
        $category = Category::find($id);
        $category->category_name = $request['category_name'];
        $category->category_title = $request['category_title'];
        $category->category_description = $request['category_description'];
        $category->location = $request['category_location'];
        if (isset($request['filename'])) {
            \Log::debug($request);
            $category->image_name = $request['filename'];
        }

        $category->save();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();

        Session::flash('message', "Successfully deleted " . $category->category_name .  " category!");
        return redirect('admin/categories');
    }
}
