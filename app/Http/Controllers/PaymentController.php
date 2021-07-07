<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Physician;
use App\Models\Hospital;
use App\Http\Resources\PaymentResource;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentsExport;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payments');
    }

    /**
     * Export Payments from a Physician
     *
     * @param Physician $physician
     * @return void
     */
    public function export_physician(Physician $physician) {
        return Excel::download(new PaymentsExport($physician->payments), 'payments_physician_'.$physician->id.'_'.now()->format('Ymd\THis').'.xls', \Maatwebsite\Excel\Excel::XLS);
    }

    /**
     * Export Payments from a Physician
     *
     * @param Hospital $hospital
     * @return void
     */
    public function export_hospital(Hospital $hospital) {
        return Excel::download(new PaymentsExport($hospital->payments), 'payments_hospital_'.$hospital->id.'_'.now()->format('Ymd\THis').'.xls', \Maatwebsite\Excel\Excel::XLS);
    }

    /**
     * Display the specified Payment.
     *
     * @param Payment $payment
     * @return \Illuminate\Http\Response
     */
    public function api_show(Payment $payment) {
        return new PaymentResource($payment);
    }

    /**
     * Display results of a search of Payments.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function api_search(Request $request) {
        return PaymentResource::collection(Payment::take(10)->get());
    }

    /**
     * Search for a Recipient of a Payment
     *
     * @param Request $request
     * @param string $recipient
     * @return \Illuminate\Http\Response
     */
    public function api_search_recipient(Request $request) {
        $validated = $request->validate([
            'q' => 'required|regex:/^([a-z0-9\.\-\s]+)?$/i'
        ]);

        // Find FirstName and LastName from potentially FirstName MiddleName LastName Suffix
        // If both a FirstName and LastName are found, compare against a Full Name field, if just one name is found, search for FirstName OR LastName.
        preg_match('/^([a-z\.-]+)\s*(?:[a-z\.-]+\s+)?([a-z\.-]+)?(?:\s+[a-z\.-]+)?$/i', $validated['q'], $names);
        if(isset($names[2])) {
            $physicians = Physician::where(DB::raw("concat(first_name, ' ', last_name)"),'like',"%$names[1] $names[2]%")->get();
        } else if(isset($names[1])) {
            $physicians = Physician::where('first_name','like',"%$names[1]%")->orWhere('last_name','like',"%$names[1]%")->get();
        } else {
            $physicians = collect();
        }

        $hospitals = Hospital::where('name','like',"%$validated[q]%")->get();

        // Concatenate Hospitals and Physicians, then convert them to records.
        $output = $physicians->concat($hospitals)->take(10);
        foreach($output as $key => $val) {
            $output[$key] = $val->toRecord(false, true);
        }

        return $output->toArray();
    }

    /**
     * Show payments to a Physician
     *
     * @param Request $request
     * @param Physician $physician
     * @return \Illuminate\Http\Response
     */
    public function api_show_physician_payments(Physician $physician) {
        return PaymentResource::collection($physician->payments);
    }

    /**
     * Show payments to a Hospital
     *
     * @param Request $request
     * @param Hospital $hospital
     * @return \Illuminate\Http\Response
     */
    public function api_show_hospital_payments(Hospital $hospital) {
        return PaymentResource::collection($hospital->payments);
    }
}
