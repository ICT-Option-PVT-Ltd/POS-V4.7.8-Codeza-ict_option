<title>Print Serial Number Labels</title>
<button class="btn btn-success" onclick="window.print()">Print</button>
<div id="preview_body" style="width: {{$generation->label_width}}in;">
@foreach($serial_numbers as $serial)
    <div style="height:{{$generation->sticker_height}}in;width:{{$generation->sticker_width}}in;display:inline-block;border:1px dotted #ccc;box-sizing:border-box;text-align:center;vertical-align:top;overflow:hidden;">
        <div style="font-size:12px;">{{$serial->serial_number}}</div>
        <img style="max-width:95%;height:{{max(0.3, $generation->sticker_height - 0.25)}}in" src="data:image/png;base64,{{DNS1D::getBarcodePNG($serial->serial_number, $generation->barcode_type, 2, 40, array(39, 48, 54), true)}}">
    </div>
@endforeach
</div>
<style>@media print {.btn{display:none!important;}} @page {size: {{$generation->label_width}}in {{$generation->label_height}}in; margin:0;}</style>
