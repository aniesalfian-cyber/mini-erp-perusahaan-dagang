<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Faktur Penjualan - {{ $invoice->invoice_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 20mm 25mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #222222;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1E3A2B;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #1E3A2B;
            letter-spacing: 0.5px;
        }
        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            color: #1E3A2B;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-box {
            background-color: #F8F7F4;
            border: 1px solid #DDD8CF;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #1E3A2B;
            color: #FFFFFF;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #E5E1DA;
            padding: 8px 10px;
            font-size: 10.5px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .total-box {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .total-box td {
            padding: 5px 8px;
        }
        .grand-total {
            background-color: #1E3A2B;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 13px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            text-align: center;
            width: 40%;
        }
        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #333333;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

    <!-- Header Perusahaan & Judul Dokumen -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="company-title">ERP PRIMA JAYA PERKASA</div>
                <div style="font-size: 10px; color: #555555; margin-top: 2px;">
                    Distributor & Perdagangan Besar Bahan Bangunan Berkualitas<br>
                    Jl. Daan Mogot Raya KM 12 No. 88, Kalideres, Jakarta Barat<br>
                    Telp: (021) 5439-8800 | Email: finance@primajaya.com
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="invoice-title">FAKTUR PENJUALAN</div>
                <div style="font-size: 12px; font-weight: bold; font-family: monospace; color: #333333; margin-top: 4px;">
                    {{ $invoice->invoice_no }}
                </div>
                <div style="font-size: 10px; color: #666666; margin-top: 2px;">
                    Status: <strong style="color: {{ $invoice->payment_status === 'PAID' ? '#1E3A2B' : '#A85D3B' }};">{{ $invoice->payment_status }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Informasi Pelanggan & Metadata Dokumen -->
    <table class="meta-table">
        <tr>
            <td style="width: 52%; vertical-align: top; padding-right: 15px;">
                <div class="meta-box">
                    <strong style="font-size: 10px; text-transform: uppercase; color: #777777;">Ditujukan Kepada Pelanggan:</strong><br>
                    <strong style="font-size: 12px; color: #1E3A2B;">{{ $invoice->customer->name }}</strong><br>
                    <span style="font-family: monospace; font-size: 10px; color: #555555;">Kode: {{ $invoice->customer->code }}</span><br>
                    <span style="color: #444444;">{{ $invoice->customer->address ?? '-' }}</span><br>
                    <span style="color: #444444;">Telp: {{ $invoice->customer->phone ?? '-' }}</span>
                </div>
            </td>
            <td style="width: 48%; vertical-align: top;">
                <div class="meta-box">
                    <table style="width: 100%; font-size: 10.5px;">
                        <tr>
                            <td style="color: #666666; width: 45%;">Tanggal Faktur:</td>
                            <td class="font-mono" style="font-weight: bold;">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666666;">Jatuh Tempo:</td>
                            <td class="font-mono" style="font-weight: bold; color: #8B0000;">{{ $invoice->due_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666666;">No. Surat Jalan (DO):</td>
                            <td class="font-mono">{{ $invoice->deliveryOrder->do_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666666;">No. Pesanan (SO):</td>
                            <td class="font-mono">{{ $invoice->salesOrder->order_no ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Daftar Barang -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">No</th>
                <th style="width: 48%;">Deskripsi Barang / Produk Dagang</th>
                <th style="width: 12%;" class="text-right">Kuantitas</th>
                <th style="width: 17%;" class="text-right">Harga Satuan (Rp)</th>
                <th style="width: 17%;" class="text-right">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $idx => $item)
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                    </td>
                    <td class="text-right font-mono">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Ringkasan Finansial Tagihan -->
    <table class="total-box">
        <tr>
            <td style="color: #555555;">Subtotal Barang:</td>
            <td class="text-right font-mono" style="font-weight: bold;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="color: #555555;">PPN (0%):</td>
            <td class="text-right font-mono">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="grand-total">
            <td style="padding: 7px 10px;">TOTAL TAGIHAN:</td>
            <td class="text-right font-mono" style="padding: 7px 10px;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
        </tr>
        @if($invoice->paid_amount > 0)
            <tr>
                <td style="color: #006400; padding-top: 5px;">Sudah Dibayar:</td>
                <td class="text-right font-mono" style="color: #006400; font-weight: bold; padding-top: 5px;">(Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }})</td>
            </tr>
            <tr style="border-top: 1px dashed #888888;">
                <td style="color: #8B0000; font-weight: bold;">SISA PIUTANG:</td>
                <td class="text-right font-mono" style="color: #8B0000; font-weight: bold;">Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <!-- Informasi Rekening Pembayaran -->
    <div style="background-color: #F8F7F4; border: 1px solid #DDD8CF; border-radius: 6px; padding: 10px 14px; font-size: 10px; margin-bottom: 25px;">
        <strong>Informasi Pembayaran Melalui Transfer Rekening Bank Resmi:</strong><br>
        Bank Central Asia (BCA) KCP Daan Mogot | No. Rek: <strong class="font-mono">882-019-2811</strong> a/n PT Prima Jaya Perkasa<br>
        Bank Mandiri Cabang Jakarta Barat | No. Rek: <strong class="font-mono">118-00-992182-1</strong> a/n PT Prima Jaya Perkasa<br>
        <span style="color: #666666;">Harap cantumkan nomor faktur <strong>{{ $invoice->invoice_no }}</strong> pada berita transfer pembayaran Anda.</span>
    </div>

    <!-- Tanda Tangan Pengesahan -->
    <table class="signature-table">
        <tr>
            <td class="signature-box" style="text-align: left;">
                <div>Tanda Terima Pelanggan / Proyek,</div>
                <div class="signature-line" style="margin-left: 0;"></div>
                <div style="font-size: 10px; color: #555555; margin-top: 3px;">Nama Terang & Stempel Perusahaan</div>
            </td>
            <td style="width: 20%;"></td>
            <td class="signature-box" style="text-align: right;">
                <div>Hormat Kami, PT Prima Jaya Perkasa</div>
                <div class="signature-line" style="margin-right: 0;"></div>
                <div style="font-size: 10px; color: #555555; margin-top: 3px;">Divisi Keuangan & Akuntansi</div>
            </td>
        </tr>
    </table>

</body>
</html>
