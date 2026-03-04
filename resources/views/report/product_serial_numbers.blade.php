@extends('layouts.app')
@section('title', 'Product Serial Numbers Report')
@section('content')
<section class="content-header"><h1>Product Serial Numbers Report</h1></section>
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
    {!! Form::open(['url' => action('ProductSerialNumberReportController@index'), 'method' => 'get']) !!}
    <div class="row">
        <div class="col-md-3">{!! Form::label('product_id', 'Product:') !!}{!! Form::select('product_id', $products, request('product_id'), ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}</div>
        <div class="col-md-2">{!! Form::label('status', 'Status:') !!}{!! Form::select('status', ['available' => 'Available', 'sold' => 'Sold'], request('status'), ['class' => 'form-control', 'placeholder' => 'All']) !!}</div>
        <div class="col-md-2">{!! Form::label('from_date', 'From:') !!}{!! Form::date('from_date', request('from_date'), ['class' => 'form-control']) !!}</div>
        <div class="col-md-2">{!! Form::label('to_date', 'To:') !!}{!! Form::date('to_date', request('to_date'), ['class' => 'form-control']) !!}</div>
        <div class="col-md-2" style="margin-top:25px;"><button class="btn btn-primary" type="submit">Filter</button></div>
    </div>
    {!! Form::close() !!}
    @endcomponent

    <div class="row">
        <div class="col-md-3"><div class="small-box bg-green"><div class="inner"><h3>{{$summary['available']}}</h3><p>Available Serial Numbers</p></div></div></div>
        <div class="col-md-3"><div class="small-box bg-red"><div class="inner"><h3>{{$summary['sold']}}</h3><p>Sold Serial Numbers</p></div></div></div>
    </div>

    @component('components.widget', ['class' => 'box-primary'])
    <table class="table table-bordered table-striped">
        <thead><tr><th>#</th><th>Product</th><th>Serial Number</th><th>Status</th><th>Sold Invoice</th><th>Sold At</th></tr></thead>
        <tbody>
            @foreach($serial_numbers as $row)
            <tr>
                <td>{{$row->id}}</td>
                <td>{{$row->product_name}}</td>
                <td>{{$row->serial_number}}</td>
                <td>{{$row->status}}</td>
                <td>{{$row->invoice_no}}</td>
                <td>{{$row->sold_at}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{$serial_numbers->appends(request()->all())->links()}}
    @endcomponent
</section>
@endsection
