<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductSerialNumber;
use App\ProductSerialNumberGeneration;
use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSerialNumberController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');

        $query = ProductSerialNumber::leftJoin('products as p', 'product_serial_numbers.product_id', '=', 'p.id')
            ->leftJoin('variations as v', 'product_serial_numbers.variation_id', '=', 'v.id')
            ->where('product_serial_numbers.business_id', $business_id)
            ->select('product_serial_numbers.*', 'p.name as product_name', 'v.name as variation_name');

        if (!empty($request->product_id)) {
            $query->where('product_serial_numbers.product_id', $request->product_id);
        }
        if (!empty($request->status)) {
            $query->where('product_serial_numbers.status', $request->status);
        }

        $serial_numbers = $query->orderBy('product_serial_numbers.id', 'desc')->paginate(50);

        return view('product_serial_number.index', compact('serial_numbers', 'products'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $products = Product::where('business_id', $business_id)
            ->where('enable_sr_no', 1)
            ->pluck('name', 'id');

        $barcode_types = app('App\Utils\Util')->barcode_types();

        return view('product_serial_number.create', compact('products', 'barcode_types'));
    }

    public function getProductVariations(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)->findOrFail($request->product_id);

        $variations = Variation::join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
            ->where('variations.product_id', $product->id)
            ->select('variations.id', DB::raw("IF(pv.is_dummy = 1, 'Default', CONCAT(pv.name, ' - ', variations.name)) as name"))
            ->pluck('name', 'id');

        return response()->json(['success' => 1, 'variations' => $variations]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->validate([
            'product_id' => 'required|integer',
            'variation_id' => 'nullable|integer',
            'prefix' => 'nullable|string|max:100',
            'middle_fix' => 'nullable|string|max:100',
            'post_fix' => 'nullable|string|max:100',
            'start_from' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'number_padding' => 'required|integer|min:1|max:12',
            'barcode_type' => 'required|string|max:20',
            'sticker_width' => 'required|numeric|min:0.1',
            'sticker_height' => 'required|numeric|min:0.1',
            'label_width' => 'required|numeric|min:0.1',
            'label_height' => 'required|numeric|min:0.1',
            'labels_in_row' => 'required|integer|min:1|max:10',
        ]);

        $business_id = $request->session()->get('user.business_id');

        DB::beginTransaction();
        try {
            $generation = ProductSerialNumberGeneration::create([
                'business_id' => $business_id,
                'product_id' => $input['product_id'],
                'variation_id' => $input['variation_id'] ?? null,
                'prefix' => $input['prefix'] ?? null,
                'middle_fix' => $input['middle_fix'] ?? null,
                'post_fix' => $input['post_fix'] ?? null,
                'start_from' => $input['start_from'],
                'quantity' => $input['quantity'],
                'number_padding' => $input['number_padding'],
                'barcode_type' => $input['barcode_type'],
                'sticker_width' => $input['sticker_width'],
                'sticker_height' => $input['sticker_height'],
                'label_width' => $input['label_width'],
                'label_height' => $input['label_height'],
                'labels_in_row' => $input['labels_in_row'],
                'created_by' => $request->session()->get('user.id'),
            ]);

            $generated_count = 0;
            for ($i = 0; $i < $input['quantity']; $i++) {
                $number = str_pad($input['start_from'] + $i, $input['number_padding'], '0', STR_PAD_LEFT);
                $serial = ($input['prefix'] ?? '') . ($input['middle_fix'] ?? '') . $number . ($input['post_fix'] ?? '');

                if (!ProductSerialNumber::where('serial_number', $serial)->exists()) {
                    ProductSerialNumber::create([
                        'business_id' => $business_id,
                        'product_id' => $input['product_id'],
                        'variation_id' => $input['variation_id'] ?? null,
                        'generation_id' => $generation->id,
                        'serial_number' => $serial,
                        'serial_order' => $i + 1,
                        'status' => 'available',
                    ]);
                    $generated_count++;
                }
            }

            $generation->generated_count = $generated_count;
            $generation->save();

            DB::commit();

            return redirect()->action('ProductSerialNumberController@print', [$generation->id])
                ->with('status', ['success' => 1, 'msg' => 'Serial numbers generated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function print($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $generation = ProductSerialNumberGeneration::where('business_id', $business_id)->findOrFail($id);
        $serial_numbers = ProductSerialNumber::where('generation_id', $id)->orderBy('serial_order')->get();

        return view('product_serial_number.print', compact('generation', 'serial_numbers'));
    }
}
