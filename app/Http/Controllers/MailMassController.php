<?php

namespace App\Http\Controllers;

use App\Mail\GMailer; 
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Jobs\ProcessMassmail;
use App\Models\MailMass;
use App\Models\Newsletter;
use App\Libs\MassMail;

class MailMassController extends Controller
{
    public function index()
    {
        $massmails=MailMass::all();
        return view('admin.massmail',['pagename'=>'Mass Mail','massmails'=>$massmails]);
    }

     /**
     * Show the form for creating a new resource.
     *
     * return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.massmails.create',['pagename'=>'New Mass Mailer','categories'=>$categories]);
    }

    public function startMassMail() {
        ProcessMassmail::dispatch()->onConnection('sqs');
        $massmails=MailMass::all();
        return view('admin.massmail',['pagename'=>'Mass Mail','massmails'=>$massmails]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * param  \Illuminate\Http\Request  $request
     * return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'title' => 'required',
            'massmails' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        $massmail = MailMass::find($id);
        $is_active = isset($request['active']) && $request['active'] == 'on' ? 1 : 0;

        $data = array(
            'title' => $request['title'],
            'content' => $request['massmails'],
            'category' => serialize($request['category']),
            'is_active' => $is_active
        );

        $id = MailMass::create($data);

        return redirect("admin/massmail");
    }

    public function edit($id)
    {
        $categories = Category::all();
        $massmail = MailMass::find($id);
        return view('admin/massmails.edit',['pagename'=>'Edit Mass Mailer', 'massmail'=>$massmail,'categories'=>$categories]);
    }

    public function loadTemplate() {
        $filename = base_path().'/public/template/mass-mail-tinymce.html';
        $file = fopen($filename,'r') or die("Unable to open file!");
        $template = fread($file,filesize($filename));
        fclose($file);

        return response()->json($template);
    }

    /**
     * Update the specified resource in storage.
     *
     * param  \Illuminate\Http\Request  $request
     * param  int  $id
     * return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = \Validator::make($request->all(),[
            'title' => 'required',
            'massmails' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }
        $is_active = isset($request['active']) && $request['active'] == 'on' ? 1 : 0;

        if ($request['category']) {
            $category = serialize($request['category']);
        } else $category = '';

        $data = array(
            'title' => $request['title'],
            'content' => $request['massmails'],
            'category' => $category,
            'is_active' => $is_active
        );

        
        $massmail = MailMass::find($id)->update($data);
        return redirect("admin/massmail/$id/edit");
    }

    public function getProductsByCategory(Request $request) {
        
        $response='';
        
        if ($request->ajax()) {
            $response = MassMail::process($request);
        }

        return response()->json($response);
    }
}
