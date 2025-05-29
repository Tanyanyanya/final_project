<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\courses;
use App\Models\sub_categories;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 


class Buy_Controller extends Controller
{
    public function Admin_all_coupon(){

        $discount = Coupon::latest()->get();
        return view('rending.discount.discount_all',compact('coupon'));

    } /// End Method 

    public function Admin_add_coupon(){
        return view('rending.discount.discount_add');
    }/// End Method 

    public function Admin_store_coupon(Request $response){
        
        Coupon::insert([
            'dis_coup_name' => strtoupper($response->dis_coup_name),
            'discount_int' => $response->discount_int,
            'discount_validity' => $response->discount_validity,
            'created_at' => Carbon::now(),

        ]);

        $send_notif = array(
            'message' => 'Inserted :)',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.all.coupon')->with($send_notif);  


    }/// End Method 

    public function Admin_edit_coupon($id){

        $discount = Coupon::find($id);
        return view('rending.discount.discount_edit',compact('coupon'));

    }/// End Method 


    public function Admin_update_coupon(Request $response){

        $discount_id = $response->id;
        
        Coupon::find($discount_id)->update([
            'dis_coup_name' => strtoupper($response->dis_coup_name),
            'discount_int' => $response->discount_int,
            'discount_validity' => $response->discount_validity,
            'created_at' => Carbon::now(),

        ]);

        $send_notif = array(
            'message' => 'Updated',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.all.coupon')->with($send_notif);  


    }/// End Method 

    public function Admin_delete_coupon($id){
 
        Coupon::find($id)->delete();

        $send_notif = array(
            'message' => 'Deleted',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif);

    }/// End Method 


    public function Teacher_all_coupon(){
        $id = Auth::user()->id;
        $discount = Coupon::where('teacher_id',$id)->latest()->get();
        return view('teacher.discount.discount_all',compact('coupon'));

    }/// End Method 


    public function Teacher_add_coupon(){
        $id = Auth::user()->id;
        $courses = courses::where('teacher_id',$id)->get();
        return view('teacher.discount.discount_add',compact('courses'));

    }// End Method 

    public function Teacher_store_coupon(Request $response){

        Coupon::insert([
            'dis_coup_name' => strtoupper($response->dis_coup_name),
            'discount_int' => $response->discount_int,
            'discount_validity' => $response->discount_validity,
            'teacher_id' => Auth::user()->id,
            'courses_id' => $response->courses_id,
            'created_at' => Carbon::now(),
        ]);

        $send_notif = array(
            'message' => 'Coupon Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('teacher.all.coupon')->with($send_notif);
         //return $this->validity->gt(now());

    }// End Method 

    public function Teacher_edit_coupon($id){

        $discount = Coupon::find($id);
        $insid = Auth::user()->id;
        $courses = courses::where('teacher_id',$insid)->get();
        return view('teacher.discount.discount_edit',compact('coupon','courses'));
    }// End Method 


    public function Teacher_update_coupon(Request $response){

        $discount_id = $response->discount_id;

        Coupon::find($discount_id)->update([
            'dis_coup_name' => strtoupper($response->dis_coup_name),
            'discount_int' => $response->discount_int,
            'discount_validity' => $response->discount_validity,
            'teacher_id' => Auth::user()->id,
            'courses_id' => $response->courses_id,
            'created_at' => Carbon::now(),
        ]);

        $send_notif = array(
            'message' => 'Updated',
            'alert-type' => 'success'
        );
        return redirect()->route('teacher.all.coupon')->with($send_notif);

    }// End Method 


    public function Teacher_delete_coupon($id){

        Coupon::find($id)->delete();
        $send_notif = array(
            'message' => 'Deleted',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif);

    }// End Method 


} 