<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\pay;
use DateTime;

class Report_Controller extends Controller
{
    public function ReportView(){

        return view('rending.account.report_view');

    } // End Method 


    public function SearchByDate(Request $response){

        $date_time = new DateTime($response->date);
        $format_time = $date_time->format('d F Y');

        $pay = pay::where('buy_date',$format_time)->latest()->get();
        return view('rending.account.report_by_date',compact('pay','format_time'));

    }// End Method 


    public function SearchByMonthAndDate(Request $response){

        $month_n = $response->month;
        $year_n = $response->year_name;

        $pay = pay::where('buy_month',$month_n)->where('buy_year',$year_n)->latest()->get();
        return view('rending.account.report_by_month',compact('pay','month','year'));

    }// End Method 


    public function SearchByYear(Request $response){
 
        $year_n = $response->year;

        $pay = pay::where('buy_year',$year_n)->latest()->get();
        return view('rending.account.report_by_year',compact('pay', 'year'));

    }// End Method 


}
 