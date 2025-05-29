<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\mailSetting;
use App\Models\SiteSetting;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
 
class Mail_Set_Controller extends Controller
{
    public function MailSetting(){

        $mail_set = MailSetting::find(1);
        return view('rending.home.mail_set_update',compact('setting'));

    }// End Method 

    public function MailUpdate(Request $response){

        $mail_id = $response->id;

        MailSetting::find($mail_id)->update([
            'mailer' =>  $response->mailer,
            'host' =>  $response->host,
            'port' =>  $response->port,
            'username' =>  $response->username,
            'password' =>  $response->password,
        ]);

        $send_notif = array(
            'message' => 'Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($send_notif);  

    }// End Method 


    public function SiteFooterSetting(){

        $site = SiteSetting::find(1);
        return view('rending.site_web.site_update',compact('site'));

    }// End Method 

    public function UpdateSiteFooter(Request $response){
        
        $site_home_id = $response->id;

            if ($response->file('logo')) {

                $img_manager = new ImageManager(new Driver());
                $name_gen = hexdec(uniqid()) . '.' . $response->file('logo')->getClientOriginalExtension();
                $img = $img_manager->read($response->file('logo'));
                $img = $img->resize(125,30)->toJpeg(80)->save(base_path('public/footer/'.$name_gen));
                $save_url = 'upload/footer' . $name_gen;    
    
            SiteSetting::find($site_home_id)->update([
                'phone' => $response->phone, 
                'email' => $response->email, 
                'address' => $response->address, 
                'logo' => $save_url,        
    
            ]);
    
            $send_notif = array(
                'message' => 'Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($send_notif);  
        
        } else {

            SiteSetting::find($site_home_id)->update([
                'phone' => $response->phone, 
                'email' => $response->email, 
                'address' => $response->address, 
                'facebook' => $response->facebook,   
            ]);
    
            $send_notif = array(
                'message' => 'Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($send_notif);  

        } // end else 

    }// End Method 

}
 