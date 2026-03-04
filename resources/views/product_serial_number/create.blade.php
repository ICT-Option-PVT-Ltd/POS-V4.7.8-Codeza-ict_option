@extends('layouts.app')
@section('title', 'Generate Product Serial Numbers')

@section('content')
<section class="content-header"><h1>Generate Product Serial Numbers</h1></section>
<section class="content">
    {!! Form::open(['url' => action('ProductSerialNumberController@store'), 'method' => 'post']) !!}
    @component('components.widget', ['class' => 'box-primary'])
    <div class="row">
        <div class="col-md-4">{!! Form::label('product_id', 'Product:*') !!}{!! Form::select('product_id', $products, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select'), 'id' => 'product_id']) !!}</div>
        <div class="col-md-4">{!! Form::label('variation_id', 'Variation:') !!}{!! Form::select('variation_id', [], null, ['class' => 'form-control select2', 'id' => 'variation_id']) !!}</div>
        <div class="col-md-4">{!! Form::label('barcode_type', 'Barcode Type:*') !!}{!! Form::select('barcode_type', $barcode_types, 'C128', ['class' => 'form-control', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('prefix', 'Prefix:') !!}{!! Form::text('prefix', null, ['class' => 'form-control']) !!}</div>
        <div class="col-md-3">{!! Form::label('middle_fix', 'Middle Fix:') !!}{!! Form::text('middle_fix', null, ['class' => 'form-control']) !!}</div>
        <div class="col-md-3">{!! Form::label('post_fix', 'Post Fix:') !!}{!! Form::text('post_fix', null, ['class' => 'form-control']) !!}</div>
        <div class="col-md-3">{!! Form::label('number_padding', 'Number Padding:*') !!}{!! Form::number('number_padding', 6, ['class' => 'form-control', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('start_from', 'Start From:*') !!}{!! Form::number('start_from', 1, ['class' => 'form-control', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('quantity', 'Generate Quantity:*') !!}{!! Form::number('quantity', 100, ['class' => 'form-control', 'required']) !!}</div>

        <div class="col-md-3">{!! Form::label('sticker_width', 'Sticker Width (in):*') !!}{!! Form::number('sticker_width', 2, ['class' => 'form-control', 'step' => '0.001', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('sticker_height', 'Sticker Height (in):*') !!}{!! Form::number('sticker_height', 1, ['class' => 'form-control', 'step' => '0.001', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('label_width', 'Label Width (in):*') !!}{!! Form::number('label_width', 4, ['class' => 'form-control', 'step' => '0.001', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('label_height', 'Label Height (in):*') !!}{!! Form::number('label_height', 1, ['class' => 'form-control', 'step' => '0.001', 'required']) !!}</div>
        <div class="col-md-3">{!! Form::label('labels_in_row', 'Labels In Row:*') !!}{!! Form::number('labels_in_row', 2, ['class' => 'form-control', 'required']) !!}</div>
    </div>
    <div class="row"><div class="col-md-12 text-right" style="margin-top:15px;"><button type="submit" class="btn btn-primary">Generate & Print</button></div></div>
    @endcomponent
    {!! Form::close() !!}
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
