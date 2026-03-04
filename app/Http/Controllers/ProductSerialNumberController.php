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

        $barcode_types = ['CODE128' => 'CODE128'];

        $settings = [
            'paper_width_mm' => 76,
            'label_width_mm' => 25,
            'label_height_mm' => 12,
            'label_position' => 'RIGHT',
            'x_offset_mm' => 0,
            'y_offset_mm' => 0,
            'gap_mm' => 2,
            'copies' => 1,
            'barcode_format' => 'CODE128',
            'barcode_height_mm' => 6.5,
            'barcode_margin_mm' => 0,
            'barcode_font' => 0,
        ];

        $variations = collect();
        if (!empty(old('product_id'))) {
            $this_product = Product::where('business_id', $business_id)->find(old('product_id'));
            if (!empty($this_product)) {
                $variations = Variation::join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                    ->where('variations.product_id', $this_product->id)
                    ->select('variations.id', DB::raw("IF(pv.is_dummy = 1, 'Default', CONCAT(pv.name, ' - ', variations.name)) as name"))
                    ->pluck('name', 'id');
            }
        }

        return view('product_serial_number.create', compact('products', 'barcode_types', 'settings', 'variations'));
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
            'prefix' => 'nullable|string|max:30',
            'middle_fix' => 'nullable|string|max:100',
            'post_fix' => 'nullable|string|max:100',
            'separator' => 'nullable|string|max:3',
            'start_from' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1|max:2000',
            'number_padding' => 'required|integer|min:0|max:12',

            'paper_width_mm' => 'required|numeric|min:40|max:80',
            'label_width_mm' => 'required|numeric|min:10|max:76',
            'label_height_mm' => 'required|numeric|min:8|max:40',
            'label_position' => 'required|in:RIGHT,LEFT,CENTER',
            'x_offset_mm' => 'required|numeric|min:-30|max:30',
            'y_offset_mm' => 'required|numeric|min:-30|max:30',
            'gap_mm' => 'required|numeric|min:0|max:10',
            'copies' => 'required|integer|min:1|max:10',

            'barcode_format' => 'required|in:CODE128',
            'barcode_height_mm' => 'required|numeric|min:3|max:10',
            'barcode_margin_mm' => 'required|numeric|min:0|max:2',
            'barcode_font' => 'required|in:0,1',
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
                'separator' => $input['separator'] ?? '-',
                'start_from' => $input['start_from'],
                'quantity' => $input['quantity'],
                'number_padding' => $input['number_padding'],
                'barcode_type' => $input['barcode_format'],
                'paper_width_mm' => $input['paper_width_mm'],
                'sticker_width' => $input['label_width_mm'],
                'sticker_height' => $input['label_height_mm'],
                'label_width' => $input['label_width_mm'],
                'label_height' => $input['label_height_mm'],
                'labels_in_row' => 1,
                'label_position' => $input['label_position'],
                'x_offset_mm' => $input['x_offset_mm'],
                'y_offset_mm' => $input['y_offset_mm'],
                'gap_mm' => $input['gap_mm'],
                'copies' => $input['copies'],
                'barcode_height_mm' => $input['barcode_height_mm'],
                'barcode_margin_mm' => $input['barcode_margin_mm'],
                'barcode_font' => $input['barcode_font'],
                'created_by' => $request->session()->get('user.id'),
            ]);

            $generated_count = 0;
            for ($i = 0; $i < $input['quantity']; $i++) {
                $number = str_pad((string) ($input['start_from'] + $i), $input['number_padding'], '0', STR_PAD_LEFT);

                $serial = $number;
                if (!empty($input['prefix'])) {
                    $serial = $input['prefix'] . ($input['separator'] ?? '-') . $number;
                }
                if (!empty($input['middle_fix'])) {
                    $serial = $input['middle_fix'] . $serial;
                }
                if (!empty($input['post_fix'])) {
                    $serial .= $input['post_fix'];
                }

                $serial = mb_substr($serial, 0, 60);

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
        $serial_numbers = ProductSerialNumber::where('generation_id', $id)->orderBy('serial_order')->pluck('serial_number')->toArray();

        $paperW = (float) ($generation->paper_width_mm ?? 76);
        $labelW = (float) ($generation->sticker_width ?? 25);

        if ($generation->label_position === 'LEFT') {
            $left = 0;
        } elseif ($generation->label_position === 'CENTER') {
            $left = max(0, ($paperW - $labelW) / 2);
        } else {
            $left = max(0, $paperW - $labelW);
        }

        $left = max(0, $left + (float) $generation->x_offset_mm);

        return view('product_serial_number.print', [
            'serials' => $serial_numbers,
            'generation' => $generation,
            'label_left_mm' => $left,
        ]);
    }
}
