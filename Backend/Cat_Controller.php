<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\categories;
use App\Models\sub_categories;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class Cat_Controller extends Controller
{
    
    public function get_categories_with_subcateg_bl($id)
    {
        $cat_name = categories::with('subcateg_bl')->findOrFail($id);
        return response()->json($cat_name);
    }

    public function search_categories(Request $response)
    {
        $keyword = $response->input('query');
        $results = categories::where('cat_name', 'like', '%' . $keyword . '%')->get();
        return view('rending.categories.search_result', compact('results', 'keyword'));
    }

    public function listcateg_bl()
    {
        $categ_bl = categories::('created_at')->get();
        return view('categories.show', compact('categ_bl'));
    }

    public function show_created_categories()
    {
        return view('categories.home');
    }

    public function save_categories(Request $response)
    {
        if ($response->hasFile('image')) {
            $file_name = uniqid() . '.' . $response->image->extension();
            $image_name_path = 'upload/cat_img/' . $file_name;

            $img_manager = new ImageManager(new Driver());
            $img = $img_manager->read($response->image);
            $img->resize(380, 272)->toJpeg(80)->save(public_path($image_name_path));
        }

        categories::create([
            'cat_name' => $response->cat_name,
            'cat_slug' => \Str::slug($response->cat_name),
            'image' => $image_name Path ?? null,
        ]);

        return redirect()->route('categories.list')->with('success', 'Added :)');
    }

    public function show_edited_categories($id)
    {
        $cat_name = categories::findOrFail($id);
        return view('rending.categories.edit', compact('categories'));
    }

    public function update_categories_data(Request $response)
    {
        $cat_name = categories::findOrFail($response->id);

        if ($response->hasFile('image')) {
            if ($cat_name->image && file_exists(public_path($cat_name->image))) {
                unlink(public_path($cat_name->image));
            }

            $file_name = uniqid() . '.' . $response->image->extension();
            $path = 'upload/categories/' . $file_name;

            $img_manager = new ImageManager(new Driver());
            $img = $img_manager->read($response->image);
            $img->resize(380, 272)->toJpeg(80)->save(public_path($path));

            $cat_name->update([
                'cat_name' => $response->cat_name,
                'cat_slug' => \Str::slug($response->cat_name),
                'image' => $path,
            ]);
        } else {
            $cat_name->update([
                'cat_name' => $response->cat_name,
                'cat_slug' => \Str::slug($response->cat_name),
            ]);
        }

        return redirect()->route('categories.list')->with('success', 'Updated');
    }

    public function remove_categories($id)
    {
        $cat_name = categories::findOrFail($id);
        if ($cat_name->image && file_exists(public_path($cat_name->image))) {
            unlink(public_path($cat_name->image));
        }

        $cat_name->delete();

        return back()->with('success', 'Removed');
    }

    // sub_categories part_section

    public function view_all_subcateg_bl()
    {
        $sub_categ_bl = sub_categories::latest()->get();
        return view('rending.sub_categories.home', compact('subcateg_bl'));
    }

    public function show_add_sub_categories_form()
    {
        $categ_bl = categories::latest()->get();
        return view('rending.sub_categories.create', compact('categ_bl'));
    }

    public function store_new_sub_categories(Request $response)
    {
        sub_categories::create([
            'cat_id' => $response->cat_id,
            'sub_cat_name' => $response->sub_cat_name,
            'subcat_slug' => \Str::slug($response->sub_cat_name),
        ]);

        return redirect()->route('sub_categories.list')->with('success', 'Added');
    }

    public function show_edit_sub_categories($id)
    {
        $sub_categories = sub_categories::findOrFail($id);
        $categ_bl = categories::latest()->get();
        return view('rending.sub_categories.edit', compact('sub_categories', 'categ_bl'));
    }

    public function update_sub_categories_details(Request $response)
    {
        sub_categories::findOrFail($response->id)->update([
            'cat_id' => $response->cat_id,
            'sub_cat_name' => $response->sub_cat_name,
            'subcat_slug' => \Str::slug($response->sub_cat_name),
        ]);

        return redirect()->route('sub_categories.list')->with('success', 'sub_categories updated.');
    }

    public function delete_sub_categories_by_id($id)
    {
        sub_categories::findOrFail($id)->delete();
        return back()->with('success', 'Deleted');
    }


}