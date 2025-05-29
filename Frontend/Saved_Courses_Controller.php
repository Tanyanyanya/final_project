<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saved; 
use Illuminate\Support\Facades\Auth; 


class Saved_Courses_Controller extends Controller
{
    public function AddToSaved(Request $response, $courses_id){

        if (Auth::check()) {
           $exists = Saved::where('user_id',Auth::id())->where('courses_id',$courses_id)->first();

           if (!$exists) {
            Saved::insert([
                'user_id' => Auth::id(),
                'courses_id' => $courses_id,
            ]);
            return response()->json(['success']);
           }
        }else{
            return response()->json(['error']);
        } 

    } // End Method 


    public function AllSaved(){

        return view('home.saved.all_saved');

    }// End Method 


    public function GetSavedCourses(){

        $Saved = Saved::with('courses')->where('user_id',Auth::id())->latest()->get();

        $wishQty = Saved::count();

        return response()->json(['Saved' => $saved, 'wishQty' => $likeQty]);

    }// End Method 

    public function RemoveSaved($id){

        Saved::where('user_id',Auth::id())->where('id',$id)->delete();
        return response()->json(['success' => 'Deleted']);

    }// End Method 

}