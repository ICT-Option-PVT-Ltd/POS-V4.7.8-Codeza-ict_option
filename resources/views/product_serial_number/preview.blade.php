@extends('layouts.app')
@section('title', 'Generated Serial Numbers Preview')

@section('content')
<section class="content-header">
    <h1>Generated Serial Numbers</h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <div class="alert alert-info">
                <strong>Total generated:</strong> {{ count($serials) }}
            </div>

            <div style="max-height:320px; overflow:auto; border:1px solid #ddd; padding:10px; margin-bottom:15px;">
                @foreach($serials as $serial)
                    <div>{{ $serial }}</div>
                @endforeach
            </div>

            <div class="btn-group">
                {!! Form::open(['url' => action('ProductSerialNumberController@saveGenerated'), 'method' => 'post', 'style' => 'display:inline-block;']) !!}
                    <button type="submit" class="btn btn-success">Save to DB</button>
                {!! Form::close() !!}

                <a href="{{ action('ProductSerialNumberController@printPreview') }}" class="btn btn-primary">Print</a>
                <a href="{{ action('ProductSerialNumberController@create') }}" class="btn btn-default">Back</a>
            </div>
        </div>
    </div>
</section>
@endsection
