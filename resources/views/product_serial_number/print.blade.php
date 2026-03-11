<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Print Labels</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

  <style>
    body{font-family:Arial, sans-serif; margin:0; padding:16px; background:#f7f7f7;}
    .topbar{position:sticky; top:0; background:#fff; border:1px solid #ddd; border-radius:10px; padding:12px; margin-bottom:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between;}
    .btn{padding:10px 14px; border:0; border-radius:10px; cursor:pointer;}
    .btn-primary{background:#16a34a; color:#fff;}
    .btn-gray{background:#374151; color:#fff; text-decoration:none; display:inline-block;}
    .meta{font-size:13px; color:#333;}
    .meta b{color:#000;}

    @media print { body{background:#fff; padding:0;} .topbar{display:none;} }

    @page {
      size: {{ $generation->paper_width_mm }}mm {{ $generation->sticker_height + $generation->gap_mm }}mm;
      margin-top: 0mm;
    }

    .paper{ width: {{ $generation->paper_width_mm }}mm; margin: 0mm auto; background:#fff; }
    .label-row{ width: {{ $generation->paper_width_mm }}mm; height: {{ $generation->sticker_height + $generation->gap_mm }}mm; page-break-after: always; page-break-inside: avoid; overflow: hidden; }

    .label{
      position: relative;
      width: {{ $generation->sticker_width }}mm;
      height: {{ $generation->sticker_height }}mm;
      margin-top: {{ $generation->y_offset_mm }}mm;
      margin-left: {{ $label_left_mm }}mm;
      display: flex;
      flex-direction: column;
      justify-content: right;
      align-items: right;
      text-align: center;
      box-sizing: border-box;
      overflow: hidden;
    }

    .serial-top{font-size:8pt; font-weight:700; line-height:1; margin:0; padding:0;}
    .barcode-wrap{width:100%; margin:1mm 0 0.5mm 0; display:flex; justify-content:center;}
    svg.barcode{width:95%; height: {{ $generation->barcode_height_mm }}mm; display:block;}
    .serial-bottom{font-size:7pt; line-height:1; margin:0; padding:0;}
    .gap{height: {{ $generation->gap_mm }}mm;}
  </style>
</head>

<body>
  <div class="topbar">
    <div class="meta">
      <div><b>Paper:</b> {{ $generation->paper_width_mm }}mm | <b>Label:</b> {{ $generation->sticker_width }}×{{ $generation->sticker_height }}mm</div>
      <div><b>Position:</b> {{ $generation->label_position }} | <b>X:</b> {{ $generation->x_offset_mm }}mm | <b>Y:</b> {{ $generation->y_offset_mm }}mm | <b>Gap:</b> {{ $generation->gap_mm }}mm</div>
      <div><b>Total:</b> {{ count($serials) }} | <b>Copies:</b> {{ $generation->copies }}</div>
    </div>
    <div>
      <button class="btn btn-primary" onclick="window.print()">Print Now</button>
      <a class="btn btn-gray" href="{{ action('ProductSerialNumberController@create') }}">Back</a>
    </div>
  </div>

  <div class="paper">
    @for($copy=0; $copy < (int)$generation->copies; $copy++)
      @foreach($serials as $index => $s)
        <div class="label-row">
          <div class="label">
            <div class="serial-top">{{ $s }}</div>
            <div class="barcode-wrap">
              <svg class="barcode" data-serial="{{ $s }}" id="barcode_{{ $copy }}_{{ $index }}"></svg>
            </div>
            <div class="serial-bottom">{{ $s }}</div>
          </div>
        </div>
        <div class="gap"></div>
      @endforeach
    @endfor
  </div>

  <script>
    (function () {
      const format = "{{ $generation->barcode_type ?: 'CODE128' }}";
      const showText = {{ (int)$generation->barcode_font }} === 1;
      const marginMm = parseFloat("{{ $generation->barcode_margin_mm }}") || 0;
      const marginPx = Math.round(marginMm * 4);

      document.querySelectorAll("svg.barcode").forEach((svg) => {
        const value = svg.getAttribute("data-serial") || "";

        try {
          JsBarcode(svg, value, {
            format: format,
            displayValue: showText,
            fontSize: 10,
            height: 50,
            margin: marginPx,
            marginTop: 0,
            marginBottom: 0,
            marginLeft: marginPx,
            marginRight: marginPx,
          });
        } catch (e) {
          svg.outerHTML = '<div style="font-size:7pt;padding:1mm;">' + value + '</div>';
        }
      });
    })();
  </script>
</body>
</html>
