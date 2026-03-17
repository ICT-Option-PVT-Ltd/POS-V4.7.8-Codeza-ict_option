<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductSerialNumber;
use App\BusinessLocation;
use Illuminate\Http\Request;

class ProductSerialNumberReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');
        $locations = BusinessLocation::forDropdown($business_id);

        $query = ProductSerialNumber::leftJoin('products as p', 'product_serial_numbers.product_id', '=', 'p.id')
            ->leftJoin('transactions as t', 'product_serial_numbers.sold_transaction_id', '=', 't.id')
            ->leftJoin('business_locations as bl', 'product_serial_numbers.location_id', '=', 'bl.id')
            ->where('product_serial_numbers.business_id', $business_id)
            ->select('product_serial_numbers.*', 'p.name as product_name', 't.invoice_no', 'bl.name as location_name');

        if (!empty($request->product_id)) {
            $query->where('product_serial_numbers.product_id', $request->product_id);
        }

        if (!empty($request->location_id)) {
            $query->where('product_serial_numbers.location_id', $request->location_id);
        }

        if (!empty($request->status)) {
            $query->where('product_serial_numbers.status', $request->status);
        }

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereDate('product_serial_numbers.created_at', '>=', $request->from_date)
                ->whereDate('product_serial_numbers.created_at', '<=', $request->to_date);
        }

        $serial_numbers = $query->orderBy('product_serial_numbers.id', 'desc')->paginate(100);

        $summary = [
            'available' => (clone $query)->where('product_serial_numbers.status', 'available')->count(),
            'sold' => (clone $query)->where('product_serial_numbers.status', 'sold')->count(),
            'damaged' => (clone $query)->where('product_serial_numbers.status', 'damaged')->count(),
        ];

        return view('report.product_serial_numbers', compact('products', 'locations', 'serial_numbers', 'summary'));
    }
}
