@extends('layouts.app')

@section('title', 'Table Bills')

@section('content')
<section class="content no-print">
    <section class="content-header">
        <h1>Table Bills
            <small>Ongoing and available table status</small>
        </h1>
    </section>

    <div class="box box-primary">
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Location</label>
                        <select id="table_bills_location" class="form-control select2" style="width: 100%;">
                            @foreach($business_locations as $bl_id => $bl_name)
                                <option value="{{ $bl_id }}" @if((int)$bl_id === (int)$location_id) selected @endif>{{ $bl_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8 text-right" style="padding-top: 25px;">
                    <a href="{{ action('SellPosController@create') }}" class="btn btn-success">
                        <i class="fa fa-plus-circle"></i> New POS Bill
                    </a>
                </div>
            </div>

            <div class="row">
                @forelse($tables as $table)
                    @php
                        $bill = $ongoing_bill_by_table->get($table->id);
                        $is_ongoing = !empty($bill);
                        $card_class = $is_ongoing ? 'ongoing-card' : 'available-card';
                        $status_text = $is_ongoing ? 'Ongoing bill' : 'Available';
                        $action_text = $is_ongoing ? 'Resume Bill' : 'Start Bill';
                        $open_url = $is_ongoing
                            ? action('SellPosController@edit', [$bill->id]) . '?lock_table_selection=1&from_table_bills=1'
                            : action('SellPosController@create', ['res_table_id' => $table->id, 'lock_table_selection' => 1, 'from_table_bills' => 1]);
                    @endphp

                    <div class="col-md-3 col-sm-4 col-xs-6">
                        <a href="{{ $open_url }}" class="table-bill-card {{ $card_class }}">
                            <div class="table-title">{{ $table->name }}</div>
                            <div class="table-status">{{ $status_text }}</div>

                            @if($is_ongoing)
                                <div class="table-summary">
                                    <div><b>Invoice:</b> {{ $bill->invoice_no }}</div>
                                    <div><b>Items:</b> {{ $bill->sell_lines_count }}</div>
                                    <div><b>Total:</b> @format_currency($bill->final_total)</div>
                                    <div><b>Payment:</b> {{ ucfirst($bill->payment_status ?? 'due') }}</div>
                                    <div><b>Customer:</b> {{ optional($bill->contact)->name }}</div>
                                </div>
                            @else
                                <div class="table-summary">
                                    <div><b>Ready for new bill</b></div>
                                </div>
                            @endif
                            <div class="table-action">{{ $action_text }}</div>
                        </a>
                    </div>
                @empty
                    <div class="col-md-12">
                        <p class="text-muted">No tables found for this location.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

@section('css')
<style>
    .table-bill-card {
        display: block;
        border-radius: 20px;
        min-height: 170px;
        padding: 12px;
        margin-bottom: 15px;
        border: 2px solid #111;
        color: #fff;
        text-decoration: none !important;
    }

    .table-bill-card.available-card {
        background: #00a65a;
    }

    .table-bill-card.ongoing-card {
        background: #dd4b39;
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .table-status {
        font-size: 12px;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .table-summary {
        font-size: 12px;
        line-height: 1.6;
    }

    .table-action {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        border-top: 1px solid rgba(255, 255, 255, 0.35);
        padding-top: 6px;
    }
</style>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('#table_bills_location').on('change', function() {
            var location_id = $(this).val();
            window.location = "{{ action('SellPosController@tableBills') }}?location_id=" + location_id;
        });
    });
</script>
@endsection
