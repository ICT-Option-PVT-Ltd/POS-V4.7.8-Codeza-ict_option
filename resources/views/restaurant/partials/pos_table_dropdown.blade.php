@if($tables_enabled)
<div class="col-sm-4">
	<div class="form-group">
		<div class="input-group">
			<span class="input-group-addon">
				<i class="fa fa-table"></i>
			</span>
			<select name="res_table_id" id="res_table_id" class="form-control" @if(!empty($lock_table_selection)) disabled @endif>
				<option value="">@lang('restaurant.select_table')</option>
				@foreach($tables as $table_id => $table_name)
					@php
						$is_occupied = in_array($table_id, $occupied_table_ids ?? []);
						$is_selected = !empty($view_data['res_table_id']) && (int)$view_data['res_table_id'] === (int)$table_id;
						$is_disabled = $is_occupied && !$is_selected;
					@endphp
					<option value="{{ $table_id }}"
						@if($is_selected) selected @endif
						@if($is_disabled) disabled @endif
						style="{{ $is_occupied ? 'background-color:#dd4b39;color:#fff;' : 'background-color:#00a65a;color:#fff;' }}">
						{{ $table_name }} - {{ $is_occupied ? 'Ongoing bill' : 'Available' }}
					</option>
				@endforeach
			</select>
			@if(!empty($lock_table_selection))
				<input type="hidden" name="res_table_id" value="{{ $view_data['res_table_id'] ?? '' }}">
			@endif
		</div>
	</div>
</div>
@endif
@if($waiters_enabled)
<div class="col-sm-4">
	<div class="form-group">
		<div class="input-group">
			<span class="input-group-addon">
				<i class="fa fa-user-secret"></i>
			</span>
			{!! Form::select('res_waiter_id', $waiters, $view_data['res_waiter_id'], ['class' => 'form-control', 'placeholder' => __('restaurant.select_service_staff'), 'id' => 'res_waiter_id', 'required' => $is_service_staff_required ? true : false]); !!}
			@if(!empty($pos_settings['inline_service_staff']))
			<div class="input-group-btn">
                <button type="button" class="btn btn-default bg-white btn-flat" id="select_all_service_staff" data-toggle="tooltip" title="@lang('lang_v1.select_same_for_all_rows')"><i class="fa fa-check"></i></button>
            </div>
            @endif
		</div>
	</div>
</div>
@endif

@if(!empty($ongoing_table_bills) && count($ongoing_table_bills) > 0)
<div class="clearfix"></div>
<div class="col-sm-8">
	<div class="alert alert-warning mb-0">
		<strong>Ongoing table bills:</strong>
		<ul class="mb-0 pl-20">
			@foreach($ongoing_table_bills as $ongoing_bill)
				<li>
					{{ $ongoing_bill->table_name }} - {{ $ongoing_bill->invoice_no }} - @lang('sale.total'): @format_currency($ongoing_bill->final_total)
				</li>
			@endforeach
		</ul>
	</div>
</div>
@endif
