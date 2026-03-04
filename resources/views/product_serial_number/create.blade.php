@extends('layouts.app')
@section('title', 'Serial Number Generator')

@section('content')
<section class="content-header"><h1>Serial Numbers Generate & Print</h1></section>
<section class="content">
  <div class="box box-primary">
    <div class="box-body">
      {!! Form::open(['url' => action('ProductSerialNumberController@store'), 'method' => 'post']) !!}

      <h4>Product</h4>
      <div class="row">
        <div class="col-md-4">
          {!! Form::label('product_id', 'Product:*') !!}
          {!! Form::select('product_id', $products, old('product_id'), ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select'), 'id' => 'product_id']) !!}
        </div>
        <div class="col-md-4">
          {!! Form::label('variation_id', 'Variation:') !!}
          {!! Form::select('variation_id', ['' => 'Default'] + collect($variations)->toArray(), old('variation_id'), ['class' => 'form-control select2', 'id' => 'variation_id']) !!}
        </div>
      </div>

      <hr>
      <h4>Serial generator</h4>
      <div class="row">
        <div class="col-md-3">{!! Form::label('prefix', 'Prefix (optional)') !!}{!! Form::text('prefix', old('prefix'), ['class' => 'form-control', 'placeholder' => 'ABC']) !!}</div>
        <div class="col-md-2">{!! Form::label('separator', 'Separator') !!}{!! Form::text('separator', old('separator', '-'), ['class' => 'form-control']) !!}</div>
        <div class="col-md-2">{!! Form::label('middle_fix', 'Middle Fix') !!}{!! Form::text('middle_fix', old('middle_fix'), ['class' => 'form-control']) !!}</div>
        <div class="col-md-2">{!! Form::label('post_fix', 'Post Fix') !!}{!! Form::text('post_fix', old('post_fix'), ['class' => 'form-control']) !!}</div>
      </div>
      <div class="row" style="margin-top:10px;">
        <div class="col-md-2">{!! Form::label('start_from', 'Start Number') !!}{!! Form::number('start_from', old('start_from', 1), ['class' => 'form-control', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('quantity', 'Count') !!}{!! Form::number('quantity', old('quantity', 20), ['class' => 'form-control', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('number_padding', 'Padding') !!}{!! Form::number('number_padding', old('number_padding', 4), ['class' => 'form-control', 'required']) !!}</div>
      </div>

      <hr>
      <h4>Label settings</h4>
      <div class="row">
        <div class="col-md-2">{!! Form::label('paper_width_mm', 'Paper width (mm)') !!}{!! Form::number('paper_width_mm', old('paper_width_mm', $settings['paper_width_mm']), ['class' => 'form-control', 'step' => '0.1', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('label_width_mm', 'Label width (mm)') !!}{!! Form::number('label_width_mm', old('label_width_mm', $settings['label_width_mm']), ['class' => 'form-control', 'step' => '0.1', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('label_height_mm', 'Label height (mm)') !!}{!! Form::number('label_height_mm', old('label_height_mm', $settings['label_height_mm']), ['class' => 'form-control', 'step' => '0.1', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('label_position', 'Label position on roll') !!}
          {!! Form::select('label_position', ['RIGHT' => 'RIGHT', 'CENTER' => 'CENTER', 'LEFT' => 'LEFT'], old('label_position', $settings['label_position']), ['class' => 'form-control']) !!}
        </div>
      </div>
      <div class="row" style="margin-top:10px;">
        <div class="col-md-2">{!! Form::label('x_offset_mm', 'X Offset (mm)') !!}{!! Form::number('x_offset_mm', old('x_offset_mm', $settings['x_offset_mm']), ['class' => 'form-control', 'step' => '0.5', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('y_offset_mm', 'Y Offset (mm)') !!}{!! Form::number('y_offset_mm', old('y_offset_mm', $settings['y_offset_mm']), ['class' => 'form-control', 'step' => '0.5', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('gap_mm', 'Gap (mm)') !!}{!! Form::number('gap_mm', old('gap_mm', $settings['gap_mm']), ['class' => 'form-control', 'step' => '0.5', 'required']) !!}</div>
        <div class="col-md-2">{!! Form::label('copies', 'Copies') !!}{!! Form::number('copies', old('copies', $settings['copies']), ['class' => 'form-control', 'required']) !!}</div>
      </div>

      <hr>
      <h4>Barcode settings</h4>
      <div class="row">
        <div class="col-md-3">{!! Form::label('barcode_format', 'Barcode format') !!}
          {!! Form::select('barcode_format', $barcode_types, old('barcode_format', $settings['barcode_format']), ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-3">{!! Form::label('barcode_height_mm', 'Barcode height (mm)') !!}{!! Form::number('barcode_height_mm', old('barcode_height_mm', $settings['barcode_height_mm']), ['class' => 'form-control', 'step' => '0.5', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('barcode_margin_mm', 'Barcode margin (mm)') !!}{!! Form::number('barcode_margin_mm', old('barcode_margin_mm', $settings['barcode_margin_mm']), ['class' => 'form-control', 'step' => '0.1', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('barcode_font', 'Show barcode text') !!}
          {!! Form::select('barcode_font', [0 => 'No', 1 => 'Yes'], old('barcode_font', $settings['barcode_font']), ['class' => 'form-control']) !!}
        </div>
      </div>

      <div style="margin-top:15px;">
        <button type="submit" class="btn btn-primary">Generate & Open Print Page</button>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</section>
@endsection

@section('javascript')
<script>
$(document).on('change', '#product_id', function(){
    $.get("{{action('ProductSerialNumberController@getProductVariations')}}", {product_id: $(this).val()}, function(resp){
        if(resp.success){
            var options = '<option value="">Default</option>';
            $.each(resp.variations, function(id, name){ options += '<option value="'+id+'">'+name+'</option>'; });
            $('#variation_id').html(options).trigger('change');
        }
    });
});
</script>
@endsection
