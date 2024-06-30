<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function index()
    {
        $data['api_token'] = Auth::user()->api_token;
        if (Auth::check()) {
            return view('user.pages.api.index', $data);
        }
        return redirect()->route('apiDocs');
    }

    public function apiGenerate()
    {
        $user = Auth::user();
        $user->api_token = Str::random(20);
        $user->save();
        return $user->api_token;



    }



    public function e_fund(request $request)
    {

        $get_user = User::where('email', $request->email)->first() ?? null;

        if ($get_user == null) {

            return response()->json([
                'status' => false,
                'message' => 'No one user found, please check email and try again',
            ]);
        }


        User::where('email', $request->email)->increment('balance', $request->amount) ?? null;


        $amount = number_format($request->amount, 2);

        $get_depo = Transaction::where('ref_id', $request->order_id)->first() ?? null;
        if ($get_depo == null){
            $trx = new Transaction();
            $trx->ref_id = $request->order_id;
            $trx->user_id = $get_user->id;
            $trx->status = 2;
            $trx->amount = $request->amount;
            $trx->type = 2;
            $trx->save();
        }else{
            Transaction::where('ref_id', $request->order_id)->update(['status'=> 2]);
        }


        return response()->json([
            'status' => true,
            'message' => "NGN $amount has been successfully added to your wallet",
        ]);


    }


    public function verify_username(request $request)
    {

        $get_user =  User::where('email', $request->email)->first() ?? null;

        if($get_user == null){

            return response()->json([
                'username' => "Not Found, Pleas try again"
            ]);

        }

        return response()->json([
            'username' => $get_user->username
        ]);



    }


}
