<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\categories;
use App\Models\sub_categories;
use App\Models\courses;
use App\Models\c_goals;
use App\Models\coursepart_section;
 use App\Models\coursesLecture;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class courses_Controller extends Controller
{
    public function all_coursess(){
 
      $id = Auth::user()->id;
      $courses = courses::where('teacher_id',$id)->orderBy('id','desc')->get();
        return view('teacher.courses.all_courses',compact('courses'));

    }// End Method 

    public function add_courses(){

        $categ_bl = categories::latest()->get();
        return view('teacher.courses.add_courses',compact('categ_bl'));

    }// End Method 


    public function get_sub_categories($cat_name_id){

        $sub_cat = sub_categories::where('cat_id',$cat_name_id)->orderBy('sub_cat_name','ASC')->get();
        return json_encode($sub_cat);

    }// End Method 

    public function store_courses(Request $response){

        $response->validate([
            'video' => 'required|mimes:mp4|max:10000',
        ]);

        if ($response->file('courses_image')){
            $img_manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $response->file('courses_image')->getClientOriginalExtension();
            $img = $img_manager->read($response->file('courses_image'));
            $img = $img->resize(380,272);

            $img->toJpeg(80)->save(base_path('courses/img_new/'.$name_gen));
            $save_url = 'courses/img_new/' . $name_gen;

        $vid = $response->file('video');  
        $vid_name = time().'.'.$vid->getClientOriginalExtension();
        $vid->move(public_path('courses/video/'),$vid_name);
        $save_video = 'courses/video/'.$vid_name;

        $courses_id = courses::insertGetId([

            'cat_id' => $response->cat_id,
            'sub_cat_id' => $response->sub_cat_id,
            'teacher_id' => Auth::user()->id,
            'courses_title' => $response->courses_title,
            'courses_name' => $response->courses_name,
            'courses_name_slug' => strtolower(str_replace(' ', '-', $response->courses_name)),
            'description' => $response->description,
            'video' => $save_video,
            'prerequisites' => $response->prerequisites,
            'featured' => $response->featured,
            'highestrated' => $response->highestrated,
            'status' => 1,
            'courses_image' => $save_url,
            'created_at' => Carbon::now(),

        ]);
    }
        $goals_for_course = Count($response->c_goalss);
        if ($goals_for_course != NULL) {
            for ($i=0; $i < $goals_for_course; $i++) { 
                $goal_count = new c_goals();
                $goal_count->courses_id = $courses_id;
                $goal_count->goal_name = $response->c_goalss[$i];
                $goal_count->save();
            }
        }
        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.courses')->with($send_notif);  

    }// End Method 

    public function edit_courses($id){
 
        $courses = courses::find($id);
        $goals = c_goals::where('courses_id',$id)->get();
        $categ_bl = categories::latest()->get();
        $sub_categ_bl = sub_categories::latest()->get();
        return view('teacher.courses.edit_courses',compact('courses','categ_bl','subcateg_bl','goals'));

    }// End Method 

    public function update_courses(Request $response){

        $goal_new = $response->courses_id;
         
           courses::find($goal_new)->update([
            'cat_id' => $response->cat_id,
            'sub_cat_id' => $response->sub_cat_id,
            'teacher_id' => Auth::user()->id,
            'courses_title' => $response->courses_title,
            'courses_name' => $response->courses_name,
            'courses_name_slug' => strtolower(str_replace(' ', '-', $response->courses_name)),
            'description' => $response->description, 
            'featured' => $response->featured,

        ]); 

        $send_notif = array(
            'message' => 'courses Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.courses')->with($send_notif);  

    }// End Method 

    public function update_courses_image(Request $response){
 
         $courses_id = $response->id;
         $old_image = $response->old_img;

         if ($response->file('courses_image')){
            $img_manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $response->file('courses_image')->getClientOriginalExtension();
            $img = $img_manager->read($response->file('courses_image'));
            $img = $img->resize(380,272);

            $img->toJpeg(80)->save(base_path('courses/new_img/'.$name_gen));
            $save_url = 'courses/new_img/' . $name_gen;

 
         if (file_exists($old_image)) {
             unlink($old_image);
         }
 
         courses::find($courses_id)->update([
             'courses_image' => $save_url,
             'updated_at' => Carbon::now(),
         ]);
 
         $send_notif = array(
             'message' => 'Updated Successfully',
             'alert-type' => 'success'
         );
         return redirect()->back()->with($send_notif); 
        }
     }// End Method 
     
     public function update_courses_video(Request $response){
 
        $courses_id = $response->vid;
        $old_vid = $response->old_vid;

        $vid = $response->file('video');  
        $vid_name = time().'.'.$vid->getClientOriginalExtension();
        $vid->move(public_path('courses/video/'),$vid_name);
        $save_video = 'courses/video/'.$vid_name;

        if (file_exists($old_vid)) {
            unlink($old_vid);
        }

        courses::find($courses_id)->update([
            'video' => $save_video,
            'updated_at' => Carbon::now(),
        ]);

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif); 

    }// End Method 
    
    public function update_courses_goal(Request $response){
 
        $goal_new = $response->id;

        if ($response->c_goalss == NULL) {
            return redirect()->back();
        } else{

            c_goals::where('courses_id',$goal_new)->delete();

            $goals_for_course = Count($response->c_goalss);
             
                for ($i=0; $i < $goals_for_course; $i++) { 
                    $goal_count = new c_goals();
                    $goal_count->courses_id = $goal_new;
                    $goal_count->goal_name = $response->c_goalss[$i];
                    $goal_count->save();
                }  // end for
        } // end else 

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif); 

    }// End Method 

    public function delete_courses($id){
        $courses = courses::find($id);
        link($courses->courses_image);
        link($courses->video);
        courses::find($id)->delete();

        $goals_data = c_goals::where('courses_id',$id)->get();
        foreach ($goals_data as $obj) {
            $obj->goal_name;
            c_goals::where('courses_id',$id)->delete();
        }

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif); 

    }// End Method 


    public function add_lecture_vid($id){

        $courses = courses::find($id);
        $part_section_lec = course_part_section::where('courses_id',$id)->latest()->get();

        return view('teacher.courses.part_section.add_lecture',compact('courses','part_section'));

    }// End Method 

    public function add_course_part_section(Request $response){

        $goal_new = $response->id;

        course_part_section::insert([
            'courses_id' => $goal_new,
            'part_section_title' => $response->part_section_title, 
        ]);

        $send_notif = array(
            'message' => 'Added',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif);  

    }// End Method 

    public function save_new_lecture(Request $response){
 
        $lecture_section = new coursesLecture();
        $lecture_section->courses_id = $response->courses_id;
        $lecture_section->part_section_id = $response->part_section_id;
        $lecture_section->lecture_name = $response->lecture_name;
        $lecture_section->url = $response->lecture_url;
        $lecture_section->content_descrip = $response->content_descrip;
        $lecture_section->save();

        return response()->json(['success' => 'Successfully']);

    }// End Method 

    public function edit_lecture($id){
 
        $clecture = coursesLecture::find($id);
        return view('teacher.lecture.edit_courses_lecture',compact('c_lecture'));

    }// End Method 

    public function update_courses_lecture(Request $response){
        $lid = $response->id;

        courses_lecture::find($id)->update([
            'lecture_name' => $response->lecture_name,
            'url' => $response->url,
            'content_descrip' => $response->content_descrip,

        ]);
        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif);   

    }

    public function delete_lecture($id){
 
        courses_lecture::find($id)->delete();

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif);  

    }// End Method 
    
    public function delete_part_section($id){
 
        $part_section_lec = courses_part_section::find($id);

        /// Delete reated lectures 
        $part_section_lec->lectures()->delete();
        // Delete the part_section 
        $part_section_lec->delete();

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif); 

    }// End Method 

} 