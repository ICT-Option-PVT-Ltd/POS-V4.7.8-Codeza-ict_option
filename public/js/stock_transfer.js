$(document).ready(function() {
    //Add products
    if ($('#search_product_for_srock_adjustment').length > 0) {
        //Add Product
        $('#search_product_for_srock_adjustment')
            .autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        '/products/list',
                        { location_id: $('#location_id').val(), term: request.term },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
                            $(this)
                                .data('ui-autocomplete')
                                ._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        }
                    } else if (ui.content.length == 0) {
                        swal(LANG.no_products_found);
                    }
                },
                focus: function(event, ui) {
                    if (ui.item.qty_available <= 0) {
                        return false;
                    }
                },
                select: function(event, ui) {
                    if (ui.item.qty_available > 0) {
                        $(this).val(null);
                        stock_transfer_product_row(ui.item.variation_id);
                    } else {
                        alert(LANG.out_of_stock);
                    }
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
            if (item.qty_available <= 0) {
                var string = '<li class="ui-state-disabled">' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                string += ' (' + item.sub_sku + ') (Out of stock) </li>';
                return $(string).appendTo(ul);
            } else if (item.enable_stock != 1) {
                return ul;
            } else {
                var string = '<div>' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                string += ' (' + item.sub_sku + ') </div>';
                return $('<li>')
                    .append(string)
                    .appendTo(ul);
            }
        };
    }

    $('select#location_id').change(function() {
        if ($(this).val()) {
            $('#search_product_for_srock_adjustment').removeAttr('disabled');
        } else {
            $('#search_product_for_srock_adjustment').attr('disabled', 'disabled');
        }
        $('table#stock_adjustment_product_table tbody').html('');
        $('#product_row_index').val(0);
        update_table_total();
    });

    $(document).on('change', 'input.product_quantity', function() {
        var tr = $(this).closest('tr');
        update_table_row(tr);
        updateRequiredSerialLabel(tr);
    });
    $(document).on('change', 'input.product_unit_price', function() {
        update_table_row($(this).closest('tr'));
    });

    $(document).on('click', '.remove_product_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                    .closest('tr')
                    .remove();
                update_table_total();
            }
        });
    });

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    jQuery.validator.addMethod(
        'notEqual',
        function(value, element, param) {
            return this.optional(element) || value != param;
        },
        'Please select different location'
    );

    $('form#stock_transfer_form').validate({
        rules: {
            transfer_location_id: {
                notEqual: function() {
                    return $('select#location_id').val();
                },
            },
        },
    });
    $('#save_stock_transfer').click(function(e) {
        e.preventDefault();

        if ($('table#stock_adjustment_product_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        if ($('form#stock_transfer_form').valid()) {
            if (!validateStockTransferSerials()) {
                return false;
            }
            $('form#stock_transfer_form').submit();
        } else {
            return false;
        }
    });

    stock_transfer_table = $('#stock_transfer_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        ajax: '/stock-transfers',
        columnDefs: [
            {
                targets: 8,
                orderable: false,
                searchable: false,
            },
        ],
        columns: [
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'location_from', name: 'l1.name' },
            { data: 'location_to', name: 'l2.name' },
            { data: 'status', name: 'status' },
            { data: 'shipping_charges', name: 'shipping_charges' },
            { data: 'final_total', name: 'final_total' },
            { data: 'additional_notes', name: 'additional_notes' },
            { data: 'action', name: 'action' },
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_transfer_table'));
        },
    });
    $('table#stock_adjustment_product_table tbody tr').each(function(){ updateRequiredSerialLabel($(this)); });

    var detailRows = [];

    $('#stock_transfer_table tbody').on('click', '.view_stock_transfer', function() {
        var tr = $(this).closest('tr');
        var row = stock_transfer_table.row(tr);
        var idx = $.inArray(tr.attr('id'), detailRows);

        if (row.child.isShown()) {
            $(this)
                .find('i')
                .removeClass('fa-eye')
                .addClass('fa-eye-slash');
            row.child.hide();

            // Remove from the 'open' array
            detailRows.splice(idx, 1);
        } else {
            $(this)
                .find('i')
                .removeClass('fa-eye-slash')
                .addClass('fa-eye');

            row.child(get_stock_transfer_details(row.data())).show();

            // Add to the 'open' array
            if (idx === -1) {
                detailRows.push(tr.attr('id'));
            }
        }
    });

    // On each draw, loop over the `detailRows` array and show any child rows
    stock_transfer_table.on('draw', function() {
        $.each(detailRows, function(i, id) {
            $('#' + id + ' .view_stock_transfer').trigger('click');
        });
    });

    //Delete Stock Transfer
    $(document).on('click', 'button.delete_stock_transfer', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            stock_transfer_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
});

function stock_transfer_product_row(variation_id) {
    var row_index = parseInt($('#product_row_index').val());
    var location_id = $('select#location_id').val();
    $.ajax({
        method: 'POST',
        url: '/stock-adjustments/get_product_row',
        data: { row_index: row_index, variation_id: variation_id, location_id: location_id, type: 'stock_transfer' },
        dataType: 'html',
        success: function(result) {
            $('table#stock_adjustment_product_table tbody').append(result);
            var tr = $('table#stock_adjustment_product_table tbody tr').last();
            updateRequiredSerialLabel(tr);
            renderSelectedTransferSerials(tr);
            update_table_total();
            $('#product_row_index').val(row_index + 1);
        },
    });
}

function update_table_total() {
    var table_total = 0;
    $('table#stock_adjustment_product_table tbody tr').each(function() {
        var this_total = parseFloat(__read_number($(this).find('input.product_line_total')));
        if (this_total) {
            table_total += this_total;
        }
    });

    $('span#total_adjustment').text(__number_f(table_total));

    if ($('input#shipping_charges').length) {
        var shipping_charges = __read_number($('input#shipping_charges'));
        table_total += shipping_charges;
    }

    $('span#final_total_text').text(__number_f(table_total));
    $('input#total_amount').val(table_total);
}


function getTransferRowSerials(tr) {
    var raw = tr.find('.stock-transfer-serial-input').val() || '';
    return raw.split(/\n|,/).map(function(v) { return $.trim(v); }).filter(Boolean);
}

function getRequiredSerialCount(tr) {
    var qty = parseFloat(__read_number(tr.find('input.product_quantity')) || 0);
    var multiplier = parseFloat(tr.find('input.base_unit_multiplier').val() || 1);
    return Math.max(0, Math.round(qty * multiplier));
}

function renderSelectedTransferSerials(tr) {
    if (!tr.find('.serial_enabled').length) {
        return;
    }

    var required = getRequiredSerialCount(tr);
    var serials = getTransferRowSerials(tr);
    tr.find('.required-serial-count').text(required);
    tr.find('.serial-number-count').text(serials.length);

    var list = tr.find('.selected-transfer-serial-numbers-list');
    if (!serials.length) {
        list.html('');
        return;
    }

    list.html('<small class="text-muted">' + serials.join(', ') + '</small>');
}

function updateRequiredSerialLabel(tr) {
    renderSelectedTransferSerials(tr);
}

function validateStockTransferSerials() {
    var is_valid = true;
    var selected = [];

    $('table#stock_adjustment_product_table tbody tr').each(function() {
        var tr = $(this);
        if (!tr.find('.serial_enabled').length) {
            return;
        }

        var required = getRequiredSerialCount(tr);
        var serials = getTransferRowSerials(tr);

        if (serials.length !== required) {
            toastr.error('Serial numbers count must match required quantity for serial-enabled transfer items.');
            is_valid = false;
            return false;
        }

        var duplicates = serials.filter(function(item, idx) { return serials.indexOf(item) !== idx; });
        if (duplicates.length) {
            toastr.error('Duplicate serial numbers in the same row are not allowed.');
            is_valid = false;
            return false;
        }

        for (var i = 0; i < serials.length; i++) {
            if (selected.indexOf(serials[i]) !== -1) {
                toastr.error('Duplicate serial numbers across transfer rows are not allowed.');
                is_valid = false;
                return false;
            }
            selected.push(serials[i]);
        }
    });

    return is_valid;
}

$(document).on('click', '.add-transfer-serial-numbers', function() {
    var tr = $(this).closest('tr');
    var required = getRequiredSerialCount(tr);
    var serials = getTransferRowSerials(tr);

    if (!$('#transfer_serial_numbers_modal').length) {
        $('body').append('<div class="modal fade" id="transfer_serial_numbers_modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title">Add Serial Numbers</h4></div><div class="modal-body"><div class="alert alert-info">Required: <span id="transfer_serial_required">0</span></div><input type="text" id="transfer_serial_scan_input" class="form-control" placeholder="Scan serial barcode"><br><button type="button" class="btn btn-primary btn-sm" id="add_scanned_transfer_serial">Add</button><hr><ul id="transfer_serial_selected_list"></ul></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="save_transfer_serial_numbers_selection">Save</button></div></div></div></div>');
    }

    var modal = $('#transfer_serial_numbers_modal');
    modal.data('row', tr);
    modal.data('required', required);
    modal.data('serials', serials);
    modal.data('product_id', tr.find('.serial_product_id').val());

    $('#transfer_serial_required').text(required);
    var html = '';
    serials.forEach(function(serial, index) {
        html += '<li>' + serial + ' <a href="#" class="remove-transfer-serial-item" data-index="' + index + '"><i class="fa fa-times text-danger"></i></a></li>';
    });
    $('#transfer_serial_selected_list').html(html);
    $('#transfer_serial_scan_input').val('');
    modal.modal('show');
    setTimeout(function() { $('#transfer_serial_scan_input').focus(); }, 300);
});

$(document).on('click', '#add_scanned_transfer_serial', function() {
    var modal = $('#transfer_serial_numbers_modal');
    var serial_text = $.trim($('#transfer_serial_scan_input').val());
    if (!serial_text) {
        return;
    }

    var required = parseInt(modal.data('required') || 0);
    var serials = modal.data('serials') || [];
    if (serials.length >= required) {
        toastr.error('Only required quantity of serials can be added.');
        return;
    }

    if (serials.indexOf(serial_text) !== -1) {
        toastr.error('Serial number already added in this row.');
        return;
    }

    var all_selected = [];
    $('table#stock_adjustment_product_table tbody .stock-transfer-serial-input').each(function() {
        var this_row = $(this).closest('tr');
        if (this_row.is(modal.data('row'))) {
            return;
        }
        all_selected = all_selected.concat(getTransferRowSerials(this_row));
    });
    if (all_selected.indexOf(serial_text) !== -1) {
        toastr.error('Serial number already selected in another row.');
        return;
    }

    $.get('/sells/pos/get-available-serial-number', {
        serial_number: serial_text,
        product_id: modal.data('product_id'),
        location_id: $('select#location_id').val()
    }, function(resp) {
        if (!resp.success) {
            toastr.error(resp.msg);
            return;
        }

        serials.push(resp.serial.serial_number);
        modal.data('serials', serials);
        $('#transfer_serial_selected_list').append('<li>' + resp.serial.serial_number + ' <a href="#" class="remove-transfer-serial-item" data-index="' + (serials.length - 1) + '"><i class="fa fa-times text-danger"></i></a></li>');
        $('#transfer_serial_scan_input').val('').focus();
    });
});

$(document).on('click', '.remove-transfer-serial-item', function(e) {
    e.preventDefault();
    var modal = $('#transfer_serial_numbers_modal');
    var index = parseInt($(this).data('index'));
    var serials = modal.data('serials') || [];
    serials.splice(index, 1);
    modal.data('serials', serials);

    var html = '';
    serials.forEach(function(serial, idx) {
        html += '<li>' + serial + ' <a href="#" class="remove-transfer-serial-item" data-index="' + idx + '"><i class="fa fa-times text-danger"></i></a></li>';
    });
    $('#transfer_serial_selected_list').html(html);
});

$(document).on('click', '#save_transfer_serial_numbers_selection', function() {
    var modal = $('#transfer_serial_numbers_modal');
    var tr = modal.data('row');
    var serials = modal.data('serials') || [];

    tr.find('.stock-transfer-serial-input').val(serials.join(','));
    renderSelectedTransferSerials(tr);
    modal.modal('hide');
});
$(document).on('change', '#shipping_charges', function() {
    update_table_total();
});

$(document).on('change', 'select.sub_unit', function() {
    var tr = $(this).closest('tr');
    var selected_option = $(this).find(':selected');
    var multiplier = parseFloat(selected_option.data('multiplier'));
    var allow_decimal = parseInt(selected_option.data('allow_decimal'));
    tr.find('input.base_unit_multiplier').val(multiplier);

    var base_unit_price = tr.find('input.hidden_base_unit_price').val();

    var unit_price = base_unit_price * multiplier;
    var unit_price_element = tr.find('input.product_unit_price');
    __write_number(unit_price_element, unit_price);
    
    var qty_element = tr.find('input.product_quantity');
    var base_max_avlbl = qty_element.data('qty_available');
    var error_msg_line = 'pos_max_qty_error';

    if (tr.find('select.lot_number').length > 0) {
        var lot_select = tr.find('select.lot_number');
        if (lot_select.val()) {
            base_max_avlbl = lot_select.find(':selected').data('qty_available');
            error_msg_line = 'lot_max_qty_error';
        }
    }
    qty_element.attr('data-decimal', allow_decimal);
    var abs_digit = true;
    if (allow_decimal) {
        abs_digit = false;
    }
    qty_element.rules('add', {
        abs_digit: abs_digit,
    });

    if (base_max_avlbl) {
        var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
        var formated_max_avlbl = __number_f(max_avlbl);
        var unit_name = selected_option.data('unit_name');
        var max_err_msg = __translate(error_msg_line, {
            max_val: formated_max_avlbl,
            unit_name: unit_name,
        });
        qty_element.attr('data-rule-max-value', max_avlbl);
        qty_element.attr('data-msg-max-value', max_err_msg);
        qty_element.rules('add', {
            'max-value': max_avlbl,
            messages: {
                'max-value': max_err_msg,
            },
        });
        qty_element.trigger('change');
    }
    qty_element.valid();
    update_table_row($(this).closest('tr'));
});

function update_table_row(tr) {
    var quantity = parseFloat(__read_number(tr.find('input.product_quantity')));
    var multiplier = 1;

    if (tr.find('select.sub_unit').length) {
        multiplier = parseFloat(
            tr.find('select.sub_unit')
                .find(':selected')
                .data('multiplier')
        );
    }
    quantity = quantity * multiplier;
    
    var unit_price = parseFloat(tr.find('input.hidden_base_unit_price').val());
    var row_total = 0;
    if (quantity && unit_price) {
        row_total = quantity * unit_price;
    }
    tr.find('input.product_line_total').val(__number_f(row_total));
    update_table_total();
}

function get_stock_transfer_details(rowData) {
    var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');
    $.ajax({
        url: '/stock-transfers/' + rowData.DT_RowId,
        dataType: 'html',
        success: function(data) {
            div.html(data).removeClass('loading');
        },
    });

    return div;
}

$(document).on('click', 'a.stock_transfer_status', function(e) {
    e.preventDefault();
    var href = $(this).data('href');
    var status = $(this).data('status');
    $('#update_stock_transfer_status_modal').modal('show');
    $('#update_stock_transfer_status_form').attr('action', href);
    $('#update_stock_transfer_status_form #update_status').val(status);
    $('#update_stock_transfer_status_form #update_status').trigger('change');
});

$(document).on('submit', '#update_stock_transfer_status_form', function(e) {
    e.preventDefault();
    var form = $(this);
    var data = form.serialize();

    $.ajax({
        method: 'post',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(form.find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                $('div#update_stock_transfer_status_modal').modal('hide');
                toastr.success(result.msg);
                stock_transfer_table.ajax.reload();
            } else {
                toastr.error(result.msg);
            }
            $('#update_stock_transfer_status_form')
            .find('button[type="submit"]')
            .attr('disabled', false);
        },
    });
});
$(document).on('shown.bs.modal', '.view_modal', function() {
    __currency_convert_recursively($('.view_modal'));
});
