<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number ?? 'Invoice' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 24px;
        }

        .header {
            background: #4B49AC;
            color: #ffffff;
            padding: 16px;
            margin: -24px -24px 24px -24px;
        }

        .logo {
            max-height: 60px;
            max-width: 140px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .section {
            margin-bottom: 18px;
        }

        .box {
            border: 1px solid #e5e7eb;
            padding: 12px;
        }

        .title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 24px;
            background: #4B49AC;
            color: #ffffff;
            padding: 14px;
        }
    </style>
</head>
<body>

    <div class="header">
        @if(!empty($company['company_logo_path']))
            <div>
                <img src="{{ $company['company_logo_path'] }}" alt="Logo" class="logo">
            </div>
        @endif

        <div class="company-name">{{ $company['company_name'] ?? 'FishiFox' }}</div>
        <div>{{ $company['company_email'] ?? '' }}</div>
        <div>{{ $company['company_phone'] ?? '' }}</div>
        <div>{{ $company['company_address'] ?? '' }}</div>

        @if(!empty($company['invoice_header']))
            <div style="margin-top:10px;">{{ $company['invoice_header'] }}</div>
        @endif
    </div>

    <div class="title">INVOICE</div>

    <div class="section box">
        <div><strong>Invoice Number:</strong> {{ $invoice->invoice_number ?? '—' }}</div>
        <div><strong>Date:</strong> {{ !empty($invoice->billing_date) ? \Carbon\Carbon::parse($invoice->billing_date)->format('d/m/Y') : '—' }}</div>
        <div><strong>Status:</strong> {{ $invoice->status ?? '—' }}</div>
    </div>

    <div class="section box">
        <div><strong>Billed To:</strong></div>
        <div>{{ $invoice->customer_name ?? 'N/A' }}</div>
        <div>{{ $invoice->customer_email ?? 'No email provided' }}</div>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th class="right">Price</th>
                    <th class="right">Qty</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $qty = (float)($invoice->quantity ?? 1);
                    $amount = (float)($invoice->amount ?? 0);
                    $total = $amount * $qty;
                @endphp
                <tr>
                    <td>{{ $invoice->item ?? 'Service' }}</td>
                    <td>{{ $invoice->description ?? 'Service Invoice' }}</td>
                    <td class="right">{{ $invoice->currency ?? 'LKR' }} {{ number_format($amount, 2) }}</td>
                    <td class="right">{{ $qty }}</td>
                    <td class="right">{{ $invoice->currency ?? 'LKR' }} {{ number_format($total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="right"><strong>Total</strong></td>
                    <td class="right"><strong>{{ $invoice->currency ?? 'LKR' }} {{ number_format($total, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        @if(!empty($company['invoice_footer']))
            <div>{{ $company['invoice_footer'] }}</div>
        @endif

        <div style="margin-top:8px;">
            {{ $company['company_name'] ?? 'Company' }} |
            {{ $company['company_email'] ?? '' }} |
            {{ $company['company_phone'] ?? '' }}
        </div>
    </div>

</body>
</html>