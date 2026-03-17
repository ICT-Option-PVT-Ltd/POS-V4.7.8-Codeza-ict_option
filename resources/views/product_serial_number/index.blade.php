@extends('layouts.app')
@section('title', 'Product Serial Numbers')

@section('content')
<section class="content-header">
    <h1>Product Serial Numbers</h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
    {!! Form::open(['url' => action('ProductSerialNumberController@index'), 'method' => 'get']) !!}
    <div class="row">
        <div class="col-md-4">{!! Form::label('product_id', 'Product:') !!}
            {!! Form::select('product_id', $products, request('product_id'), ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
        </div>
        <div class="col-md-3">{!! Form::label('location_id', 'Location:') !!}
            {!! Form::select('location_id', $locations, request('location_id'), ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
        </div>
        <div class="col-md-3">{!! Form::label('status', 'Status:') !!}
            {!! Form::select('status', ['available' => 'Available', 'sold' => 'Sold', 'damaged' => 'Damaged'], request('status'), ['class' => 'form-control', 'placeholder' => 'All']) !!}
        </div>
        <div class="col-md-2" style="margin-top: 25px;"><button type="submit" class="btn btn-primary">Filter</button></div>
        <div class="col-md-3 text-right" style="margin-top: 25px;"><a href="{{action('ProductSerialNumberController@create')}}" class="btn btn-success">Generate Serial Numbers</a></div>
    </div>
    {!! Form::close() !!}
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
    {!! Form::open(['url' => action('ProductSerialNumberController@destroyBulk'), 'method' => 'post', 'id' => 'bulk_delete_serial_form']) !!}
    <div style="margin-bottom:10px;">
        <button type="button" class="btn btn-danger btn-sm" id="delete_selected_serials">Delete Selected (Available only)</button>
    </div>
    <table class="table table-bordered table-striped">
        <thead><tr><th><input type="checkbox" id="select_all_serials"></th><th>#</th><th>Product</th><th>Location</th><th>Serial Number</th><th>Status</th><th>Sold Invoice</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($serial_numbers as $row)
            <tr>
                <td>@if($row->status === 'available')<input type="checkbox" name="serial_ids[]" value="{{$row->id}}" class="serial-checkbox">@endif</td>
                <td>{{$row->id}}</td>
                <td>{{$row->product_name}} @if(!empty($row->variation_name)) - {{$row->variation_name}} @endif</td>
                <td>{{$row->location_name}}</td>
                <td>{{$row->serial_number}}</td>
                <td><span class="label @if($row->status == 'sold') bg-red @elseif($row->status == 'damaged') bg-yellow @else bg-green @endif">{{$row->status}}</span></td>
                <td>{{$row->sold_transaction_id}}</td>
                <td>
                    @if($row->status === 'available')
                        {!! Form::open(['url' => action('ProductSerialNumberController@destroy', [$row->id]), 'method' => 'delete', 'style' => 'display:inline-block']) !!}
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        {!! Form::close() !!}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{$serial_numbers->appends(request()->all())->links()}}
    {!! Form::close() !!}
    @endcomponent
</section>
@endsection


@section('javascript')
<script>
$(document).on('change', '#select_all_serials', function(){
    $('.serial-checkbox').prop('checked', $(this).is(':checked'));
});

$(document).on('click', '#delete_selected_serials', function(){
    if ($('.serial-checkbox:checked').length === 0) {
        toastr.error('Please select available serial numbers to delete.');
        return;
    }

    if (confirm('Are you sure?')) {
        $('#bulk_delete_serial_form').submit();
    }
});
</script>
@endsection
