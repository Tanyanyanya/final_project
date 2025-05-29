<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use Gloudemans\ShoppingShopCart\Facades\ShopCart;
use App\Models\Coupon;
use Illuminate\Support\Facades\Session;
use App\Models\pay;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\Orderdecided;
use Stripe;
use App\Models\User;
use App\Notifications\OrderComplete;
use Illuminate\Support\Facades\Notification;

class ShopCartController extends Controller
{
    public function AddToShopCart(Request $response, $courseId)
{
    $courseobj = Courses::find($courseId);

    if (!$courseAmount) {
        return response()->json(['error' => 'Course not found.']);
    }
    // Reset coupon if exists
    Session::forget('coupon');

    // Check if course is already in the ShopCart
    $exists = ShopCart::search(fn($ShopCartAmount) => $ShopCartAmount->id === $courseId);

    if ($exists->isNotEmpty()) {
        return response()->json(['error' => '']);
    }

    $cost = $course_obj->discount_cost ?? $course_obj->selling_cost;

    ShopCart::add([
        'id' => $courseId,
        'name' => $response->course_title,
        'qty' => 1,
        'cost' => $cost,
        'weight' => 1,
        'options' => [
            'image_name' => $course_obj->courses_image,
            'slug' => $response->course_slug,
            'teacher_id' => $response->teacher_id,
        ]
    ]);

    return response()->json(['success' => 'Successfully added :)']);
}



    public function ShopCartItemRemove($rowId){

        ShopCart::remove($rowId);

        if (Session::has('coupon')) {
           $dis_coup_name = Session::get('coupon')['dis_coup_name'];
           $discount = Coupon::where('dis_coup_name',$dis_coup_name)->first();

           Session::put('coupon',[
            'dis_coup_name' => $discount->dis_coup_name,
            'discount_int' => $discount->discount_int,
            'discount_amount' => round(ShopCart::total() * $discount->discount_int/100),
            'total_amount' => round(ShopCart::total() - ShopCart::total() * $discount->discount_int/100 )
        ]);

        }
        return response()->json(['success' => 'Removed']);

    }// End Method 


    public function CouponApplyInCart(Request $response){

        $discount = Coupon::where('dis_coup_name',$response->dis_coup_name)->where('discount_validity','>=',Carbon::now()->format('Y-m-d'))->first(); 

        if ($discount) {
            Session::put('coupon',[
                'dis_coup_name' => $discount->dis_coup_name,
                'discount_int' => $discount->discount_int,
                'discount_amount' => round(ShopCart::total() * $discount->discount_int/100),
                'total_amount' => round(ShopCart::total() - ShopCart::total() * $discount->discount_int/100 )
            ]);

            return response()->json(array(
                'validity' => true,
                'success' => 'Successfully'
            ));
            
        }else {
            return response()->json(['error' => 'Invaild']);
        }

    }// End Method 


    public function IsCouponApplyInCart(Request $response){

        $discount = Coupon::where('dis_coup_name',$response->dis_coup_name)->where('discount_validity','>=',Carbon::now()->format('Y-m-d'))->first(); 

        if ($discount) {
            if ($discount->courses_id == $response->courses_id && $discount->teacher_id == $response->teacher_id) {

                Session::put('coupon',[
                    'dis_coup_name' => $discount->dis_coup_name,
                    'discount_int' => $discount->discount_int,
                    'discount_amount' => round(ShopCart::total() * $discount->discount_int/100),
                    'total_amount' => round(ShopCart::total() - ShopCart::total() * $discount->discount_int/100 )
                ]);
    
                return response()->json(array(
                    'validity' => true,
                    'success' => 'Successfully'
                )); 
                 
            } 
        } else {
            return response()->json(['error' => 'Invalid']);
        }

    }// End Method 

    public function CouponCheck(){

        if (Session::has('coupon')) {
           return response()->json(array(
            'subtotal' => ShopCart::total(),
            'dis_coup_name' => session()->get('coupon')['dis_coup_name'],
            'discount_int' => session()->get('coupon')['discount_int'],
            'discount_amount' => session()->get('coupon')['discount_amount'],
            'total_amount' => session()->get('coupon')['total_amount'],
           ));
        } else{
            return response()->json(array(
                'total' => ShopCart::total(),
            ));
        }

    }// End Method 


    public function Pay(Request $response){

        $user = User::where('persona','teacher')->get();

        if (Session::has('coupon')) {
           $total_amount = Session::get('coupon')['total_amount'];
        }else {
            $total_amount = round(ShopCart::total());
        }

            $data = array(); 
            $data['name'] = $response->name;
            $data['email'] = $response->email;
            $data['phone'] = $response->phone;
            $data['address'] = $response->address;
            $data['courses_name'] = $response->courses_name;
            $ShopCartTotal = ShopCart::total();
            $ShopCarts = ShopCart::content();
        

        if ($response->cash_choosed == 'stripe') {
            return view('pay.stripe',compact('data','ShopCartTotal','ShopCarts'));
        }elseif($response->cash_choosed == 'immediately'){ 

        // Cerate a new pay Record 

        $data = new pay();
        $data->name = $response->name;
        $data->email = $response->email;
        $data->phone = $response->phone;
        $data->cash_choosed = $response->cash_choosed;
        $data->total_amount = $total_amount;
        $data->pay_type = 'Direct pay';
        $data->invoice_num = 'EOS' . mt_rand(1111111, 99999999);
        $data->buy_date = Carbon::now()->format('d F Y');
        $data->buy_month = Carbon::now()->format('F');
        $data->buy_year = Carbon::now()->format('Y');
        $data->status = 'undecided';
        $data->created_at = Carbon::now(); 
        $data->save();


       foreach ($response->courses_title as $key => $courses_title) {
        
            $existingOrder = Order::where('user_id',Auth::user()->id)->where('courses_id',$response->courses_id[$key])->first();

            if ($existingOrder) {

                $send_notif = array(
                    'message' => 'Enrolled',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($send_notif); 
            } // end if 

            $order = new Order();
            $order->pay_id = $data->id;
            $order->user_id = Auth::user()->id;
            $order->courses_id = $response->courses_id[$key];
            $order->teacher_id = $response->teacher_id[$key];
            $order->courses_title = $courses_title;
            $order->cost = $response->cost[$key];
            $order->save();

           } // end foreach 

           $response->session()->forget('ShopCart');

           $payId = $data->id;

           /// Start Send email to student ///
           $sendmail = pay::find($payId);
           $data = [
                'invoice_no' => $sendmail->invoice_no,
                'amount' => $total_amount,
                'name' => $sendmail->name,
                'email' => $sendmail->email,
           ];

           Mail::to($response->email)->send(new Orderdecided($data)); 
           /// End Send email to student /// 

           /// Send Notification 
           Notification::send($user, new OrderFinished($response->name));

           $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('index')->with($send_notif); 

        } // End Elseif 
           
       
    }// End Method 


    public function StripeTetstOrder(Request $response){
        if (Session::has('coupon_name')) {
            $total_amount = Session::get('coupon_name')['total_amount'];
         }else {
             $total_amount = round(ShopCart::total());
         }

         \Stripe\Stripe::setApiKey('sk_test_51RBzuV4NR4BNIpPPiUrqRm4fM0GN5ID1TW3imbYVMXshSBrbisq31hpny3zzCVt9ZFbLFEcU0xrOvqoG6Q2INZDI00vA5PI3pl');

         $token = $_POST['TokenWithStripe'];

         $charge = \Stripe\Charge::create([
            'cost' => $total_amount*100, 
            'description' => 'Skillery',
            'source' => $token,
            'metadata' => ['buy_id' => '3434'],
         ]); 

         $buy_id = pay::insertGetId([
            'name' => $response->name,
            'email' => $response->email,
            'phone' => $response->phone,
            'address' => $response->address,
            'total_cost' => $total_amount,
            'pay_type' => 'StripeTest',
            'invoice_num' => 'EOS' . rand(1111111, 99999999),
            'buy_date' => Carbon::now()->format('d F Y'),
            'buy_month' => Carbon::now()->format('F'),
            'buy_year' => Carbon::now()->format('Y'),
            'status' => 'undecided',
         ]);

         $ShopCarts = ShopCart::content();
         foreach ($ShopCarts as $ShopCart) {
            Order::insert([
                'pay_id' => $buy_id,
                'user_id' => Auth::user()->id,
                'courses_id' => $ShopCart->id,
                'teacher_id' => $ShopCart->options->Teacher,
                'courses_name' => $ShopCart->name,
                'cost' => $ShopCart->cost,
            ]);
         }// end foreach 

         if (Session::has('discount')) {
            Session::forget('discount');
         }
         ShopCart::destroy();

         $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('home')->with($send_notif); 

    }// End Method 



    public function BuyInShopCart(Request $response, $id){

        $courses = Courses::find($id); 
      
        // Check if the courses is already in the ShopCart
        $ShopCartObj = ShopCart::search(function ($ShopCartObj, $rowId) use ($id) {
            return $ShopCartObj->id === $id;
        });

        if ($ShopCartObj->isNotEmpty()) {
            return response()->json(['error' => 'Exsist']);
        }

        if ($courses->discount_cost == NULL) {

            ShopCart::add([
                'id' => $id, 
                'name' => $response->courses_name, 
                'qty' => 1, 
                'sell_cost' => $courses->selling_cost, 
                'weight' => 1, 
                'options' => [
                    'image_name' => $courses->courses_image,
                    'slug' => $response->courses_name_slug,
                    'Teacher' => $response->Teacher,
                ],
            ]); 

        }else{

            ShopCart::add([
                'id' => $id, 
                'name' => $response->courses_name, 
                'qty' => 1, 
                'sell_cost' => $courses->discount_cost, 
                'weight' => 1, 
                'options' => [
                    'image_name' => $courses->courses_image,
                    'slug' => $response->courses_name_slug,
                    'Teacher' => $response->Teacher,
                ],
            ]);  
        }

        return response()->json(['success' => 'Successfully']); 

    }// End Method 

    public function MarkMessageAsRead(Request $response, $send_notif_id){
 
        $user = Auth::user();
        $send_notif = $user->notifications()->where('id',$send_notif_id)->first();

        if ($send_notif) {
            $send_notif->MarkMessageAsRead();

        }
        return response()->json(['count' => $teacher->UnreadNotifications()->count()]);

    }// End Method 




}
 