<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;

class Comment_Controller extends Controller
{
    public function StoreReview(Request $response){

        $courses = $response->courses_id;
        $teacher = $response->teacher_id;

        $response->validate([
            'comment' => 'required',
        ]);

        Comment::insert([
            'courses_id' => $courses,
            'user_id' => Auth::id(),
            'message' => $response->comment,
            'stars' => $response->stars,
            'teacher_id' => $teacher,
        ]);
        return redirect()->back($send_notif); 

    }// End Method 

    public function AdminUndecidedComment(){

        $comment = Comment::where('status',0)->orderBy('id','DESC')->get();
        return view('rending.comment.undecided_review',compact('comment'));

    }// End Method 

    public function UpdateCommentStatus(Request $response){

        $reviewId = $response->input('review_id');
        $isChecked = $response->input('is_checked',0);

        $comment = Comment::find($reviewId);
        if ($comment) {
            $comment->status = $isChecked;
            $comment->save();
        }

        return response()->json(['message' => 'Successfully']);

    }// End Method 

    public function ActiveComment(){

        $Comment = Comment::where('status',1)->orderBy('id','DESC')->get();
        return view('rending.comment.active_comment',compact('comment'));

    }// End Method 

    public function AllComment(){
        $id = Auth::user()->id;
        $comment = Comment::where('teacher_id',$id)->where('status',1)->orderBy('id','DESC')->get();
        return view('comment.active',compact('comment'));


    }// End Method 


}
 