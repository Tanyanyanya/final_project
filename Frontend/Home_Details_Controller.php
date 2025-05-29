<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\categories;
use App\Models\sub_categories;
use App\Models\courses;
use App\Models\User;
use App\Models\c_goals;


class Home_Details_Controller extends Controller
{
    public function CoursesDetails($id,$slug){

        $courses = courses::find($id);
        $goals = c_goals::where('courses_id',$id)->orderBy('id','DESC')->get();

        $ins_id = $courses->teacher_id; 
        $Teachercourses = courses::where('teacher_id',$ins_id)->orderBy('id','DESC')->get();

        $categ_bl = categories::latest()->get();

        $cat_id = $courses->cat_id; 
        $relatedcourses = courses::where('cat_id',$cat_id)->where('id','!=',$id)->orderBy('id','DESC')->limit(3)->get();

        return view('courses.courses_details',compact('courses','goals','teacher_courses','categ_bl','relatedcourses'));

    } // End Method 

    public function CategoriesCourses($id, $slug){

        $courses = courses::where('cat_id',$id)->where('status','1')->get();
        $cat_name = categories::where('id',$id)->first();
        $categ_bl = categories::latest()->get();
        return view('home.categories.categories_all',compact('courses','cat','categ_bl')); 
    }// End Method 


    public function SubCatCourses($id, $slug){

        $courses = courses::where('sub_cat_id',$id)->where('status','1')->get();
        $sub_categories = sub_categories::where('id',$id)->first();
        $categ_bl = categories::latest()->get();
        return view('home.categories.sub_categories_all',compact('courses','sub_cat','categ_bl')); 
    }// End Method 


    public function TeacherDetails($id){

        $Teacher = User::find($id);
        $courses = courses::where('teacher_id',$id)->get();
        return view('home.teacher.teacher_details',compact('teacher','courses'));

    }// End Method 
    
    

} 