<?php

namespace Ihasan\Bkash\Http\Controllers;

use Ihasan\Bkash\Facades\Bkash;
use Ihasan\Bkash\Models\BkashPayment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class BkashController extends Controller
{
    /**
     * Handle the callback from bKash
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $paymentId = $request->input('paymentID');
        $status = $request->input('status');

        if ($status !== 'success') {
            return redirect()->route('bkash.failed')
                ->with('error', 'Payment was not successful')
                ->with('payment_id', $paymentId);
        }

        try {
            $payment = BkashPayment::where('payment_id', $paymentId)
                ->where('transaction_status', 'Completed')
                ->first();

            if ($payment && !empty($payment->trx_id)) {
                $response = Bkash::queryPayment($paymentId);
            } else {
                $response = Bkash::executePayment($paymentId);
            }

            $successUrl = config('bkash.redirect_urls.success');
            if ($successUrl) {
                return redirect()->to($successUrl)
                    ->with('payment', $response);
            }

            return redirect()->route('bkash.success')
                ->with('payment', $response);
        } catch (\Exception $e) {
            $failedUrl = config('bkash.redirect_urls.failed');
            if ($failedUrl) {
                return redirect()->to($failedUrl)
                    ->with('error', $e->getMessage())
                    ->with('payment_id', $paymentId);
            }

            return redirect()->route('bkash.failed')
                ->with('error', $e->getMessage())
                ->with('payment_id', $paymentId);
        }
    }

    /**
     * Display success page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function success(Request $request)
    {
        $payment = $request->session()->get('payment');

        return view('bkash::success', compact('payment'));
    }

    /**
     * Display failed page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function failed(Request $request)
    {
        $error = $request->session()->get('error');
        $paymentId = $request->session()->get('payment_id');

        return view('bkash::failed', compact('error', 'paymentId'));
    }
}
