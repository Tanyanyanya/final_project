<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BL_categ;
use App\Models\BL_new;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Carbon;

class Blog_Controller extends Controller
{
    public function listBlogcateg_bl(){
        $categ_bl = BL_categ::orderBy('id', 'desc')->get();
        return view('BL_categ.categories', compact('categ_bl'));
    }

    public function createBL_categ(Request $response){
        BL_categ::create([
            'cat_name' => $response->cat_name,
            'cat_slug' => Str::slug($response->cat_name),
        ]);

        return redirect()->back()->with([ 'message' => 'Created :)', ], 
        ResponseInterface::HTTP_OK, lang('Shared.api.receive'));
    }

    public function fetchBL_categ($id){
        $data = BL_categ::findOrFail($id);
        return response()->json($data);
    }

    public function updateBL_categ(Request $response){
        $cat_name = BL_categ::findOrFail($response->cat_id);
        $cat_name->update([
            'cat_name' => $response->cat_name,
            'cat_slug' => Str::slug($response->cat_name),
        ]);

        return redirect()->back()->with([
            'message' => 'Updated :)',
            'alert-type' => 'success'
        ], ResponseInterface::HTTP_OK, lang('Shared.api.receive'));
    }

    public function removeBL_categ($id){
        BL_categ::destroy($id);

        return redirect()->back()->with([
            'message' => 'Deleted :)',
            'alert-type' => 'success'
        ]);
    }

    public function storeNewPost(Request $response){
        $image_name_path = null;

        if ($response->hasFile('new_image')) {
            $img_manager = new ImageManager(new Driver());
            $file_name = uniqid() . '.' . $response->file('post_images')->getClientOriginalExtension();
            $image_name  = $img_manager->read($response->file('post_images'))->resize(370, 246);
            $image_name ->toJpeg(80)->save(public_path('upload/blog_img/' . $file_name));
            $image_namePath = 'upload/blog_img/' . $file_name;
        }

        BL_new::create([
            'blogcat_id' => $response->blogcat_id,
            'blog_title' => $response->blog_title,
            'blog_slug' => Str::slug($response->blog_title),
            'post_tags' => $response->post_tags,
            'post_images' => $image_name_path,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('blog.upload_new')->with([
            'message' => 'Post created successfully.',
            'alert-type' => 'success'
        ],ResponseInterface::HTTP_OK, lang('Shared.api.receive'));
    }

    public function editPostForm($id){
        $post = BL_new::findOrFail($id);
        $categ_bl = BL_categ::latest()->get();
        return view('post.edit_post', compact('post', 'categ_bl'));
    }

    public function updatePostData(Request $response){
        $post = BL_new::findOrFail($response->id);

        $data = [
            'blogcat_id' => $response->blogcat_id,
            'blog_title' => $response->blog_title,
            'blog_slug' => Str::slug($response->blog_title),
            'long_descp' => $response->long_descp,
            'post_tags' => $response->post_tags,
            'created_at' => Carbon::now(),
        ];

        if ($response->hasFile('post_image')) {
            $img_manager = new ImageManager(new Driver());
            $file_name = uniqid() . '.' . $response->file('post_image')->getClientOriginalExtension();
            $image_name  = $img_manager->read($response->file('post_image'))->resize(370, 246);
            $image_name ->toJpeg(80)->save(public_path('upload/blog_img/' . $file_name));
            $data['post_image'] = 'upload/blog_img/' . $file_name;
        }

        $post->update($data);

        return redirect()->route('blog.upload_new')->with([
            'message' => 'Updated',
            'alert-type' => 'success'
        ]);
    }

    public function removePost($id){
        $post = BL_new::findOrFail($id);
        if (file_exists(public_path($post->post_image))) {
            unlink(public_path($post->post_image));
        }
        $post->delete();

        return redirect()->back()->with([
            'message' => 'Removed',
            'alert-type' => 'success'
        ]);
    }

    public function displayPostDetails($slug){
        $post = BL_new::where('blog_slug', $slug)->firstOrFail();
        $tagArray = explode(',', $post->post_tags);
        $categ_bl = BL_categ::latest()->get();
        $lastPosts = BL_new::latest()->take(3)->get();

        return view('blog.blog_details', compact('post', 'tagArray', 'categ_bl', 'latestPosts'));
    }

    public function listBycategories($id){
        $posts = BL_new::where('blogcat_id', $id)->get();
        $cat_name = BL_categ::findOrFail($id);
        $categ_bl = BL_categ::latest()->get();
        $recent = BL_new::latest()->take(3)->get();

        return view('blog.blog_cat_list', compact('posts', 'categories', 'categ_bl', 'recent'));
    }

    public function allBlogEntries(){
        $allPosts = BL_new::latest()->paginate(2);
        $categ_bl = BL_categ::latest()->get();
        $recentPosts = BL_new::latest()->take(3)->get();

        return view('blog.blog_cat_list', compact('allPosts', 'categ_bl', 'recentPosts'));
    }

    public function showAllUsers(){
        $userList = User::where('persona', 'user')->orderByDesc('id')->get();
        return view('user.user_all', compact('userList'));
    }

    public function showAllTeachers(){
        $TeacherList = User::where('persona', 'Teacher')->orderByDesc('id')->get();
        return view('rending.user.teacher_all', compact('TeacherList'));
    }
}
