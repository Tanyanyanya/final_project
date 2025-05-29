<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\coursepart_section;
use App\Models\Examine;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;

class Examine_Controller extends Controller
{
    public function UserExamine(Request $response){

        $courses_id = $response->courses_id;
        $teacher_id = $response->teacher_id;

        Examine::insert([
            'courses_id' => $courses_id,
            'user_id' => Auth::user()->id,
            'teacher_id' => $teacher_id,
            'theme' => $response->theme,
            'examine' => $response->examine,
            'created_at' => Carbon::now(),
        ]);

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back($send_notif);  

    } // End Method 

    public function TeacherAllExamine(){

        $id = Auth::user()->id;
        $examine = Examine::where('teacher_id',$id)->where('parent_id', null)->orderBy('id','desc')->get();
        return view('teacher.examine.all_examines',compact('examine'));

    }// End Method 

    public function ExamineDetails($id){

        $examine = Examine::find($id);
        $replay = Examine::where('parent_root_id',$id)->orderBy('id','asc')->get();
        return view('teacher.examine.examine_details',compact('examine','replay'));

    }// End Method 

    public function TeacherReplayEx(Request $response){

        $answer_id = $response->answer;
        $user_id = $response->user_id;
        $courses_id = $response->courses_id;
        $teacher_id = $response->teacher_id;

        Examine::insert([
            'courses_id' => $courses_id,
            'user_id' => $user_id,
            'teacher_id' => $teacher_id,
            'parent_id' => $answer_id,
            'examine' => $response->Examine,
        ]);

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('teacher.all.examine')->with($send_notif); 


    }// End Method 



}