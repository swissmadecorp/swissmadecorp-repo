<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class RemindersController extends Controller
{
    public function create(){
        return view('admin.reminders.create',['pagename' => 'Reminder']);
    }

    public function edit($id){
        $reminder = Reminder::findOrFail($id);
        return view('admin.reminders.edit',['pagename' => 'Reminder','reminder'=>$reminder]);
    }

    public function index() {
        $reminders = Reminder::all();
        
        return view('admin.reminders',['pagename' => 'Reminders','reminders'=>$reminders,'includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    public function setReadStatus(Request $request) {

        if ($request->ajax()) {
            $reminder = Reminder::findOrFail($request['id']);
        
            $reminder->status=0;
            $reminder->save();

            return response()->json('success');
        }
    }

    public function loadReminder(Request $request) {
        if ($request->ajax()) {
            $reminder = Reminder::where('id',$request['id'])
                ->where('status',1)->first();

            $arr = $reminder->toArray();
            
            //$arr['criteria'] = 'System indicates that ' . $arr['criteria'] . ' was set for reminder for'

            unset($arr['id']);
            unset($arr['page']);
            unset($arr['status']);
            unset($arr['action']);
            if ($arr['assigned_to'] && $arr['location'])
                $criteria = sprintf('System indicates that the following watch %s was set for reminder for %s from %s on %s',$arr['criteria'],$arr['assigned_to'],$arr['location'],$reminder->created_at->format('m-d-Y'));
            elseif ($arr['assigned_to'] && !$arr['location'])
                $criteria = sprintf('System indicates that  the following watch %s was set for reminder for %s on %s',$arr['criteria'],$arr['assigned_to'],$reminder->created_at->format('m-d-Y'));
            elseif (!$arr['assigned_to'] && !$arr['location'])
                $criteria = sprintf('System indicates that  the following watch %s was set for reminder on %s',$arr['criteria'],$reminder->created_at->format('m-d-Y'));

            return response()->json($criteria);
        }
    }

    public function update(Request $request, $id) {
        $reminder = Reminder::findOrFail($id);
        $reminder->update($request->all());

        return redirect("admin/reminders");
    }

    public function store(Request $request) {
        
        $validator = \Validator::make($request->all(), [
            'page' => 'required',
            'criteria' => 'required',
            'assigned_to' => 'required',
            'location' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }
        
        $request['status'] = 1;

        Reminder::create($request->all());
        
        return redirect("admin/reminders");
    }
    
    public function loadProperties(Request $request) {
        $columns = \Schema::getColumnListing($request['page'].'s');
        return response()->json($columns);
    }

    public function show() {
        //return view('admin.reminders.show',['pagename' => 'Reminder','reminder'=>$reminder]);
    }

    public function destroy($id) {
        $reminder = Reminder::findOrFail($id);        
        $reminder->delete();
        
        \Session::flash('message', "Successfully deleted reminder!");
        return redirect('admin/reminders');
    }
}
