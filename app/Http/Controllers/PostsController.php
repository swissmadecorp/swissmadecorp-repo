<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts=Post::all();
        return view('admin.posts',['pagename'=>'Posts','posts'=>$posts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.posts.create',['pagename'=>'New Post']);
    }

    public function blog($category=null) {
        // get all the blog stuff from database
        // if a category was passed, use that
        // if no category, get all posts
            
        if ($category) {
            $post = Post::where('slug',$category)->first();
            return view('posts',array('post'=>$post));
        } else {
            $posts = Post::latest()->paginate(24);
            return view('posts',array('posts'=>$posts));
        }
        // show the view with blog posts (app/views/blog.blade.php)
    }

    public function blogs($category=null) {
        // get all the blog stuff from database
        // if a category was passed, use that
        // if no category, get all posts
            
        if ($category) {
            $post = Post::where('slug',$category)->first();
            return view('blogs',array('post'=>$post));
        } else {
            $posts = Post::latest()->paginate(24);
            return view('blogs',array('posts'=>$posts));
        }
        // show the view with blog posts (app/views/blog.blade.php)
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'title' => 'required',
            'posts' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        // if (!$request['new_id']) {
        //     Post::create([
        //         'title' => $request['title'],
        //         'subtitle' => $request['subtitle'],
        //         'post' => $request['posts']
        //     ]);
        // }
        $this->savePost($request->all());
        return redirect("admin/posts");
    }

    private function getRelatedSlugs($slug) {
        return Post::select('slug')->where('slug', 'like', $slug.'%')
            ->get();
    }

    public static function savePost($request) {
        
        $data = array(
            'title' => $request['title'],
            'subtitle' => $request['subtitle'],
            'post' => $request['posts'],
            'slug' => Str::slug($request['title'],'-')
        );

        if (isset($request['image'])) {
            $data['image'] = $request['image'];
        }

        $id = Post::create($data);

        return $id;
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
        $post=Post::find($id);
        return view('admin.posts.edit',['pagename'=>'Edit Post', 'post'=>$post]);
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
            'title' => 'required',
            'posts' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        $this->updatePost($request->all(),$id);
        return redirect("admin/posts");
    }

    public static function updatePost($request,$id=0) {
        
        $data = array(
            'title' => $request['title'],
            'subtitle' => $request['subtitle'],
            'post' => $request['posts'],
            'slug' => Str::slug($request['title'],'-')
        );

        if (isset($request['image'])) {
            $data['image'] = $request['image'];
        }
        
        // if (!$request['new_id']) {
            Post::find($id)->update($data);
        // }

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        if ($post->image) {
            if (file_exists(base_path().'/public/images/posts/'.$post->image)) 
                unlink(base_path().'/public/images/posts/'.$post->image);

            if (file_exists(base_path().'/public/images/posts/thumbs/'.$post->image)) 
                unlink(base_path().'/public/images/posts/thumbs/'.$post->image);
        }
        $post->delete();

        return redirect('admin/posts');
    }
}
