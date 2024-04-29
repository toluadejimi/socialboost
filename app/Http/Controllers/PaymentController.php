<?php

namespace App\Http\Controllers;

use App\Http\Traits\Notify;
use App\Http\Traits\Upload;
use App\Models\Fund;
use App\Models\Gateway;
use App\Models\ManualPayment;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Facades\App\Services\BasicService;
use Illuminate\Support\Facades\Auth;
use Log;

class PaymentController extends Controller
{
    use Notify, Upload;

    public function addFundRequest(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'gateway' => 'required',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $basic = (object)config('basic');
        $gate = Gateway::where('code', $request->gateway)->where('status', 1)->first();
        if (!$gate) {
            return response()->json(['error' => 'Invalid Gateway'], 422);
        }
        if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
            return response()->json(['error' => 'Please Follow Transaction Limit'], 422);
        }
        $charge = getAmount($gate->fixed_charge + ($request->amount * $gate->percentage_charge / 100));
        $payable = getAmount($request->amount + $charge);
        $final_amo = getAmount($payable * $gate->convention_rate);
        $user = auth()->user();

        $fund = $this->newFund($request, $user, $gate, $charge, $final_amo);

        session()->put('track', $fund['transaction']);

        if (1000 > $fund->gateway->id) {
            $method_currency = (checkTo($fund->gateway->currencies, $fund->gateway_currency) == 1) ? 'USD' : $fund->gateway_currency;
            $isCrypto = (checkTo($fund->gateway->currencies, $fund->gateway_currency) == 1) ? true : false;
        } else {
            $method_currency = $fund->gateway_currency;
            $isCrypto = false;
        }


        return [
            'gateway_image' => getFile(config('location.gateway.path') . $gate->image),
            'amount' => getAmount($fund->amount) . ' ' . $basic->currency_symbol,
            'charge' => getAmount($fund->charge) . ' ' . $basic->currency_symbol,
            'gateway_currency' => trans($fund->gateway_currency),
            'payable' => getAmount($fund->amount + $fund->charge) . ' ' . $basic->currency_symbol,
            'conversion_rate' => 1 . ' ' . $basic->currency . ' = ' . getAmount($fund->rate) . ' ' . $method_currency,
            'in' => trans('In') . ' ' . $method_currency . ':' . getAmount($fund->final_amount, 2),
            'isCrypto' => $isCrypto,
            'conversion_with' => ($isCrypto) ? trans('Conversion with') . $fund->gateway_currency . ' ' . trans('and final value will Show on next step') : null,
            'payment_url' => route('user.addFund.confirm'),
        ];

    }

    public function fund_manual_now(Request $request)
    {



        if ($request->receipt == null) {
            return back()->with('error', "Payment receipt is required");
        }


        $file = $request->file('receipt');
        $receipt_fileName = date("ymis") . $file->getClientOriginalName();
        $destinationPath = public_path() . 'upload/receipt';
        $request->receipt->move(public_path('upload/receipt'), $receipt_fileName);


        $pay = new Fund();
        $pay->receipt = $receipt_fileName;
        $pay->gateway_id = 999;
        $pay->status = 2;
        $pay->name = $request->name;
        $pay->user_id = Auth::id();
        $pay->amount = $request->amount;
        $pay->save();


        $message = Auth::user()->email . "| submitted payment receipt |  NGN " . number_format($request->amount) . " | on SOCIAL BOOST PLUG";
        send_notification2($message);



        return view('user.pages.confirm-pay');

    }

    public function resolve(Request $request)
    {
        $dep = Transaction::where('ref_id', $request->trx_ref)->first() ?? null;


        if ($dep == null) {
            return back()->with('error', "Transaction not Found");
        }

        if ($dep->status == 2) {
            return back()->with('error', "This Transaction has been successful");
        }


        if ($dep->status == 4) {
            return back()->with('error', "This Transaction has been resolved");
        }


        if ($dep == null) {
            return back()->with('error', "Transaction has been deleted");
        } else {

            $ref = $request->trx_ref;
            $user =  Auth::user() ?? null;

            return view('user.pages.resolve', compact('ref', 'user'));

        }
    }



    public function  resolveNow(request $request)
    {

        if ($request->trx_ref == null || $request->session_id == null) {
            return back()->with('error', "Session ID or Ref Can not be null");
        }


        $trx = Transaction::where('ref_id', $request->trx_ref)->first()->status ?? null;
        $ck_trx = (int)$trx;
        if ($ck_trx == 2) {

            $email = Auth::user()->email;
            $message =  "$email | SOCIAL BOOST PLUG  | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification2($message);

            $message =  "$email | SOCIAL BOOST PLUG  | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification($message);


            return back()->with('error', "This Transaction has been successful");
        }



        if ($ck_trx != 1) {

            $email = Auth::user()->email;
            $message =  "$email | SOCIAL BOOST PLUG  | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification2($message);



            $message =  "$email | SOCIAL BOOST PLUG | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification($message);


            return back()->with('error', "This Transaction has been successful");
        }

        if ($ck_trx == 2) {

            $email = Auth::user()->email;
            $message =  "$email |SOCIAL BOOST PLUG | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification2($message);

            $message =  "$email | SOCIAL BOOST PLUG | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification($message);



            return back()->with('error', "This Transaction has been successful");
        }


        if ($ck_trx == 4) {

            $email = Auth::user()->email;
            $message =  "$email |SOCIAL BOOST PLUG | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification2($message);

            $message =  "$email | SOCIAL BOOST PLUG | is trying to fund and a successful order with orderid $request->trx_ref";
            send_notification($message);



            return back()->with('error', "This Transaction has been resolved");
        }






        if ($ck_trx == 1) {
            $session_id = $request->session_id;
            if ($session_id == null) {
                $notify[] = ['error', "session id or amount cant be empty"];
                return back()->withNotify($notify);
            }


            $curl = curl_init();
            $databody = array(
                'session_id' => "$session_id",
                'ref' => "$request->trx_ref"

            );

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://web.enkpay.com/api/resolve',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $databody,
            ));

            $var = curl_exec($curl);
            curl_close($curl);
            $var = json_decode($var);


            $messager = $var->message ?? null;
            $status = $var->status ?? null;
            $trx = $var->trx ?? null;
            $amount = $var->amount ?? null;

            if ($status == true) {
                User::where('id', Auth::id())->increment('balance', $var->amount);
                Transaction::where('ref_id', $request->trx_ref)->update(['status' => 2]);


                $user_email = Auth::user()->email;
                $message = "$user_email | $request->trx_ref | $session_id | $var->amount | just resolved deposit | SOCIAL BOOST PLUG";
                send_notification($message);
                send_notification2($message);


                return redirect()->route('user.addFund')->with('message', "Transaction successfully Resolved, NGN $amount added to ur wallet");
            }

            if ($status == false) {
                return back()->with('error', "$messager");
            }

            return back()->with('error', "please try again later");
        }
    }


    public function depositConfirm(Request $request)
    {
        $track = session()->get('track');
        $order = Fund::where('transaction', $track)->orderBy('id', 'DESC')->with(['gateway', 'user'])->first();
        if (is_null($order)) {
            return redirect()->route('user.addFund')->with('error', 'Invalid Fund Request');
        }
        if ($order->status != 0) {
            return redirect()->route('user.addFund')->with('error', 'Invalid Fund Request');
        }
        if (999 < $order->gateway_id) {
            return view('user.payment.manual', compact('order'));
        }

        $method = $order->gateway->code;
        try {
            $getwayObj = 'App\\Services\\Gateway\\' . $method . '\\Payment';
            $data = $getwayObj::prepareData($order, $order->gateway);
            $data = json_decode($data);

        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (isset($data->error)) {
            return back()->with('error', $data->message);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        $page_title = 'Payment Confirm';
        return view($data->view, compact('data', 'page_title', 'order'));
    }

    public function fromSubmit(Request $request)
    {
        $basic = (object)config('basic');

        $track = session()->get('track');
        $data = Fund::where('transaction', $track)->orderBy('id', 'DESC')->with(['gateway', 'user'])->first();
        if (is_null($data)) {
            return redirect()->route('user.addFund')->with('error', 'Invalid Fund Request');
        }
        if ($data->status != 0) {
            return redirect()->route('user.addFund')->with('error', 'Invalid Fund Request');
        }
        $gateway = $data->gateway;
        $params = optional($data->gateway)->parameters;


        $rules = [];
        $inputField = [];

        $verifyImages = [];

        if ($params != null) {
            foreach ($params as $key => $cus) {
                $rules[$key] = [$cus->validation];
                if ($cus->type == 'file') {
                    array_push($rules[$key], 'image');
                    array_push($rules[$key], 'mimes:jpeg,jpg,png');
                    array_push($rules[$key], 'max:2048');
                    array_push($verifyImages, $key);
                }
                if ($cus->type == 'text') {
                    array_push($rules[$key], 'max:191');
                }
                if ($cus->type == 'textarea') {
                    array_push($rules[$key], 'max:300');
                }
                $inputField[] = $key;
            }
        }

        $this->validate($request, $rules);


        $path = config('location.deposit.path') . date('Y') . '/' . date('m') . '/' . date('d');
        $collection = collect($request);

        $reqField = [];
        if ($params != null) {
            foreach ($collection as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {
                                try {
                                    $reqField[$inKey] = [
                                        'field_name' => $this->uploadImage($request[$inKey], $path),
                                        'type' => $inVal->type,
                                    ];
                                } catch (\Exception $exp) {
                                    session()->flash('error', 'Could not upload your ' . $inKey);
                                    return back()->withInput();
                                }
                            }
                        } else {
                            $reqField[$inKey] = $v;
                            $reqField[$inKey] = [
                                'field_name' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }
            $data->detail = $reqField;
        } else {
            $data->detail = null;
        }

        $data->created_at = Carbon::now();
        $data->status = 2; // pending
        $data->update();


        $msg = [
            'username' => $data->user->username,
            'amount' => getAmount($data->amount),
            'currency' => $basic->currency,
            'gateway' => $gateway->name
        ];
        $action = [
            "link" => route('admin.user.fundLog', $data->user_id),
            "icon" => "fa fa-money-bill-alt text-white"
        ];
        $this->adminPushNotification('PAYMENT_REQUEST', $msg, $action);

        session()->flash('success', 'You request has been taken.');
        return redirect()->route('user.fund-history');
    }

    public function gatewayIpn(Request $request, $code, $trx = null, $type = null)
    {

        if (isset($request->m_orderid)) {
            $trx = $request->m_orderid;
        }
        if (isset($request->MERCHANT_ORDER_ID)) {
            $trx = $request->MERCHANT_ORDER_ID;
        }
        if (isset($request->payment_ref)) {
            $payment_ref = $request->payment_ref;
        }

        if (isset($request->payment_request_id) && $code == "instamojo") {
            $payment_ref = $request->payment_request_id;
            $gateway = Gateway::where('code', $code)->first();
            $salt = trim($gateway->parameters->salt);


            $imData = $request->all();
            $macSent = $imData['mac'];
            unset($imData['mac']);
            ksort($imData, SORT_STRING | SORT_FLAG_CASE);
            $mac = hash_hmac("sha1", implode("|", $imData), $salt);

            $order = Fund::where('btc_wallet', $payment_ref)->orderBy('id', 'desc')->with(['gateway', 'user'])->first();

            if ($macSent == $mac && $imData['status'] == "Credit" && $order->status == '0') {
                BasicService::preparePaymentUpgradation($order);
            }
            session()->flash('success', 'You request has been processing.');
            return redirect()->route('user.fund-history');
        }


        if ($code == 'coinbasecommerce') {
            $input = fopen("php://input", "r");
            $gateway = Gateway::where('code', $code)->first();

            $postdata = file_get_contents("php://input");

            $res = json_decode($postdata);

            if (isset($res->event)) {
                $order = Fund::where('transaction', $res->event->data->metadata->trx)->orderBy('id', 'DESC')->with(['gateway', 'user'])->first();
                $sentSign = $request->header('X-Cc-Webhook-Signature');

                $sig = hash_hmac('sha256', $postdata, $gateway->parameters->secret);

                if ($sentSign == $sig) {
                    if ($res->event->type == 'charge:confirmed' && $order->status == 0) {
                        \App\Services\BasicService::preparePaymentUpgradation($order);
                    }
                }
            }

            session()->flash('success', 'You request has been processing.');
            return redirect()->route('user.fund-history');
        }

        try {
            $gateway = Gateway::where('code', $code)->first();
            if (!$gateway) throw new \Exception('Invalid Payment Gateway.');
            if (isset($trx)) {
                $order = Fund::where('transaction', $trx)->orderBy('id', 'desc')->first();
                if (!$order) throw new \Exception('Invalid Payment Request.');
            }
            if (isset($payment_ref)) {
                $order = Fund::where('btc_wallet', $payment_ref)->orderBy('id', 'desc')->with(['gateway', 'user'])->first();
                if (!$order) throw new \Exception('Invalid Payment Request.');
            }


            $getwayObj = 'App\\Services\\Gateway\\' . $code . '\\Payment';
            $data = $getwayObj::ipn($request, $gateway, @$order, @$trx, @$type);


        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($data['status'], $data['msg']);
        }
    }

    public function success()
    {
        return view('success');
    }

    public function failed()
    {
        return view('failed');
    }

    /**
     * @param Request $request
     * @param $user
     * @param $gate
     * @param $charge
     * @param $final_amo
     * @return Fund
     */
    public function newFund(Request $request, $user, $gate, $charge, $final_amo): Fund
    {
        $fund = new Fund();
        $fund->user_id = $user->id;
        $fund->gateway_id = $gate->id;
        $fund->gateway_currency = strtoupper($gate->currency);
        $fund->amount = $request->amount;
        $fund->charge = $charge;
        $fund->rate = $gate->convention_rate;
        $fund->final_amount = getAmount($final_amo);
        $fund->btc_amount = 0;
        $fund->btc_wallet = "";
        $fund->transaction = strRandom();
        $fund->try = 0;
        $fund->status = 0;
        $fund->save();
        return $fund;
    }


    public function verify_payment(request $request)
    {

        $trx_id = $request->trans_id;
        $ip = $request->ip();
        $status = $request->status;


        if ($status == 'failed') {


            $message = Auth::user()->email . "| Cancled |  NGN " . number_format($request->amount) . " | with ref | $trx_id |  on SOCIAL BOOST PLUG";
            send_notification2($message);


            Transaction::where('ref_id', $trx_id)->where('status', 1)->update(['status' => 3]);

            return redirect()->route('user.addFund')->with('error', 'Transaction Declined');
        }




        $trxstatus = Transaction::where('ref_id', $trx_id)->first()->status ?? null;

        if ($trxstatus == 2) {

            $message =  Auth::user()->email . "| is trying to fund  with | " . number_format($request->amount, 2) . "\n\n IP ====> " . $request->ip();
            send_notification($message);

            $message =  Auth::user()->email . "| on SOCIAL BOOST PLUG | is trying to fund  with | " . number_format($request->amount, 2) . "\n\n IP ====> " . $request->ip();
            send_notification2($message);

            return redirect()->route('user.addFund')->with('error', 'Transaction already confirmed or not found');
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://web.enkpay.com/api/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('trans_id' => "$trx_id"),
        ));

        $var = curl_exec($curl);
        curl_close($curl);
        $var = json_decode($var);

        $status1 = $var->detail ?? null;
        $amount = $var->price ?? null;




        if ($status1 == 'success') {

            $chk_trx = Transaction::where('ref_id', $trx_id)->first() ?? null;
            if ($chk_trx == null) {
                return back()->with('error', 'Transaction not processed, Contact Admin');
            }

            Transaction::where('ref_id', $trx_id)->update(['status' => 2]);
            User::where('id', Auth::id())->increment('balance', $amount);

            $message =  Auth::user()->email . "| just funded NGN" . number_format($request->amount, 2) . " on Log market";
            send_notification($message);





            $order_id = $trx_id;
            $databody = array('order_id' => "$order_id");

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://web.enkpay.com/api/resolve-complete',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $databody,
            ));

            $var = curl_exec($curl);
            curl_close($curl);
            $var = json_decode($var);


            $message = Auth::user()->email . "| Just funded |  NGN " . number_format($request->amount) . " | with ref | $order_id |  on SOCIAL BOOST PLUG";
            send_notification2($message);




            return redirect()->route('user.addFund')->with('message', "Wallet has been funded with $amount");
        }

        return redirect()->route('user.addFund')->with('error', 'Transaction already confirmed or not found');
    }

}
