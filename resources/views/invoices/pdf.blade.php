<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 24px;
        }

        .top {
            background: #4B49AC;
            color: #ffffff;
            padding: 20px;
            margin: -24px -24px 24px -24px;
        }

        .logo {
            max-height: 60px;
            max-width: 140px;
            display: block;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .header-text {
            margin-top: 10px;
            font-size: 11px;
            line-height: 1.6;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0 16px 0;
        }

        .box {
            border: 1px solid #e5e7eb;
            padding: 12px;
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #4B49AC;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th, td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            font-size: 11px;
            color: #374151;
        }

        .right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            color: #4B49AC;
        }

        .footer {
            margin-top: 26px;
            background: #4B49AC;
            color: #ffffff;
            padding: 16px;
        }

        .footer-text {
            margin-bottom: 8px;
            font-size: 11px;
            line-height: 1.6;
        }

        .small {
            font-size: 11px;
            line-height: 1.7;
        }
    </style>
</head>
<body>

    <div class="top">
        @if(!empty($company['company_logo_path']))
            <img src="{{ $company['company_logo_path'] }}" alt="Company Logo" class="logo">
        @endif

        <div class="company-name">{{ $company['company_name'] ?? 'FishiFox' }}</div>
        <div class="small">{{ $company['company_email'] ?? '' }}</div>
        <div class="small">{{ $company['company_phone'] ?? '' }}</div>
        <div class="small">{{ $company['company_address'] ?? '' }}</div>

        @if(!empty($company['invoice_header']))
            <div class="header-text">{{ $company['invoice_header'] }}</div>
        @endif
    </div>

    <div class="title">INVOICE</div>

    <div class="box">
        <div><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</div>
        <div><strong>Date:</strong> {{ optional($invoice->billing_date)->format('d/m/Y') ?? '—' }}</div>
        <div><strong>Status:</strong> {{ $invoice->status }}</div>
    </div>

    <div class="box">
        <div class="section-title">Billed To</div>
        <div><strong>{{ $invoice->customer_name }}</strong></div>
        <div>{{ $invoice->customer_email ?? 'No email provided' }}</div>
    </div>

    <div class="section-title">Invoice Summary</div>

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
            <tr>
                <td>{{ $invoice->item ?? 'Service' }}</td>
                <td>{{ $invoice->description ?? 'Service Invoice' }}</td>
                <td class="right">{{ $invoice->currency }} {{ number_format((float) $invoice->amount, 2) }}</td>
                <td class="right">{{ $invoice->quantity ?? 1 }}</td>
                <td class="right">
                    {{ $invoice->currency }}
                    {{ number_format(((float) $invoice->amount * (float) ($invoice->quantity ?? 1)), 2) }}
                </td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="right">Total</td>
                <td class="right">
                    {{ $invoice->currency }}
                    {{ number_format(((float) $invoice->amount * (float) ($invoice->quantity ?? 1)), 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        @if(!empty($company['invoice_footer']))
            <div class="footer-text">{{ $company['invoice_footer'] }}</div>
        @endif

        <div class="small">
            {{ $company['company_name'] ?? 'Company' }}<br>
            {{ $company['company_address'] ?? '' }}<br>
            {{ $company['company_email'] ?? '' }}<br>
            {{ $company['company_phone'] ?? '' }}<br>
            Generated on {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</body>
</html>