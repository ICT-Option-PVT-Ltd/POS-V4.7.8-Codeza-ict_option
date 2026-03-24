<?php

namespace App\Http\Controllers\Restaurant;

use App\Restaurant\ResTable;
use App\Transaction;

use App\Utils\Util;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller;

class DataController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Show the restaurant module related details in pos screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPosDetails(Request $request)
    {
        if (request()->ajax()) {
            $business_id = $request->session()->get('user.business_id');
            $location_id = $request->get('location_id');
            $selected_table_id = $request->get('selected_table_id');
            $lock_table_selection = !empty($request->get('lock_table_selection')) ? true : false;
            if (!empty($location_id)) {
                $transaction_id = $request->get('transaction_id', null);
                if (!empty($transaction_id)) {
                    $transaction = Transaction::find($transaction_id);
                    $view_data = ['res_table_id' => $transaction->res_table_id,
                            'res_waiter_id' => $transaction->res_waiter_id,
                        ];
                } else {
                    $view_data = ['res_table_id' => $selected_table_id, 'res_waiter_id' => null];
                }

                $waiters_enabled = false;
                $tables_enabled = false;
                $waiters = null;
                $tables = null;
                if ($this->commonUtil->isModuleEnabled('service_staff')) {
                    $waiters_enabled = true;
                    $waiters = $this->commonUtil->serviceStaffDropdown($business_id, $location_id);
                }
                if ($this->commonUtil->isModuleEnabled('tables')) {
                    $tables_enabled = true;
                    $tables = ResTable::where('business_id', $business_id)
                            ->where('location_id', $location_id)
                            ->pluck('name', 'id');
                }

                $ongoing_table_bills = [];
                if ($tables_enabled) {
                    $ongoing_table_bills_query = Transaction::leftJoin('res_tables as rt', 'transactions.res_table_id', '=', 'rt.id')
                        ->where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'sell')
                        ->whereNotNull('transactions.res_table_id')
                        ->where('transactions.location_id', $location_id)
                        ->where(function ($query) {
                            $query->where('transactions.is_suspend', 1)
                                ->orWhereIn('transactions.payment_status', ['due', 'partial']);
                        })
                        ->select(
                            'transactions.id',
                            'transactions.invoice_no',
                            'transactions.res_table_id',
                            'rt.name as table_name',
                            'transactions.final_total',
                            'transactions.updated_at'
                        )
                        ->orderBy('transactions.updated_at', 'desc');

                    if (!empty($transaction_id)) {
                        $ongoing_table_bills_query->where('transactions.id', '!=', $transaction_id);
                    }

                    $ongoing_table_bills = $ongoing_table_bills_query->get();
                }
                $occupied_table_ids = !empty($ongoing_table_bills) ? $ongoing_table_bills->pluck('res_table_id')->toArray() : [];
            } else {
                $tables = [];
                $waiters = [];
                $waiters_enabled = $this->commonUtil->isModuleEnabled('service_staff') ? true : false;
                $tables_enabled = $this->commonUtil->isModuleEnabled('tables') ? true : false;
                $view_data = ['res_table_id' => null, 'res_waiter_id' => null];
                $ongoing_table_bills = [];
                $occupied_table_ids = [];
            }

            $pos_settings = json_decode($request->session()->get('business.pos_settings'), true);

            $is_service_staff_required = (!empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1) ? true : false;

            return view('restaurant.partials.pos_table_dropdown')
                    ->with(compact('tables', 'waiters', 'view_data', 'waiters_enabled', 'tables_enabled', 'is_service_staff_required', 'ongoing_table_bills', 'occupied_table_ids', 'lock_table_selection'));
        }
    }

    /**
     * Save the pos screen details.
     *
     * @return null
     */
    public function sellPosStore($input)
    {
        $table_id = request()->get('res_table_id');
        $res_waiter_id = request()->get('res_waiter_id');

        Transaction::where('id', $input['transaction_id'])
            ->where('type', 'sell')
            ->where('business_id', $input['business_id'])
            ->update(['res_table_id' => $table_id,
                'res_waiter_id' => $res_waiter_id]);
    }
}
