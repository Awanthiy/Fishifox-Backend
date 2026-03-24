<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            background: #f8fafc;
        }

        .page {
            width: 100%;
            min-height: 100vh;
            background: #ffffff;
        }

        .top-bar {
            background: #4B49AC;
            padding: 28px 34px 24px 34px;
        }

        .top-bar-inner {
            width: 100%;
        }

        .top-logo {
            max-height: 70px;
            max-width: 160px;
            display: block;
            margin-bottom: 10px;
        }

        .brand {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1;
        }

        .brand-white {
            color: #ffffff;
        }

        .brand-orange {
            color: #FF8C42;
        }

        .brand-tagline {
            font-size: 10px;
            color: #ffffff;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        .header-text {
            margin-top: 14px;
            font-size: 12px;
            line-height: 1.6;
            color: #ffffff;
            max-width: 85%;
        }

        .content {
            padding: 30px 34px 0 34px;
        }

        .title-row {
            width: 100%;
            margin-bottom: 26px;
        }

        .title-left {
            float: left;
            width: 60%;
        }

        .title-right {
            float: right;
            width: 40%;
            text-align: right;
        }

        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }

        .invoice-title {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 1px;
            margin: 0 0 8px 0;
            color: #111827;
        }

        .meta-label {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .meta-value {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .divider {
            height: 6px;
            background: #4B49AC;
            margin: 24px -34px 30px -34px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: #4B49AC;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .company-summary {
            margin-bottom: 24px;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 12px;
            line-height: 1.8;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items thead th {
            text-align: left;
            font-size: 11px;
            color: #374151;
            border-bottom: 2px solid #c7d2fe;
            padding: 10px 6px;
        }

        .items tbody td {
            padding: 12px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            vertical-align: top;
        }

        .items .right {
            text-align: right;
        }

        .total-row td {
            border-bottom: none !important;
            padding-top: 14px;
            font-weight: 800;
            font-size: 15px;
        }

        .total-label {
            text-align: right;
            color: #4B49AC;
        }

        .total-value {
            text-align: right;
            color: #4B49AC;
        }

        .info-grid {
            margin-top: 34px;
            width: 100%;
        }

        .info-box {
            width: 48%;
            float: left;
        }

        .info-box.right-box {
            float: right;
            text-align: right;
        }

        .info-card {
            border-top: 2px solid #c7d2fe;
            padding-top: 12px;
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
        }

        .footer {
            margin-top: 50px;
            background: #4B49AC;
            color: #ffffff;
            padding: 22px 34px;
        }

        .footer-left {
            float: left;
            width: 50%;
        }

        .footer-right {
            float: right;
            width: 50%;
            text-align: right;
            font-size: 12px;
            line-height: 1.8;
        }

        .footer-brand {
            font-size: 28px;
            font-weight: 800;
            margin-top: 10px;
            line-height: 1;
        }

        .footer-brand-white {
            color: #ffffff;
        }

        .footer-brand-orange {
            color: #FF8C42;
        }

        .footer-sub {
            font-size: 11px;
            opacity: 0.9;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .footer-text {
            margin-top: 12px;
            font-size: 11px;
            line-height: 1.7;
            color: #ffffff;
            opacity: 0.95;
            max-width: 90%;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="top-bar">
            <div class="top-bar-inner">
                @if(!empty($company['company_logo_path']))
                    <img src="{{ $company['company_logo_path'] }}" alt="Company Logo" class="top-logo">
                @endif

                <div class="brand">
                    @if(!empty($company['company_name']))
                        <span class="brand-white">{{ $company['company_name'] }}</span>
                    @else
                        <span class="brand-white">Fishi</span><span class="brand-orange">Fox</span>
                    @endif
                </div>

                @if(!empty($company['invoice_header']))
                    <div class="header-text">
                        {{ $company['invoice_header'] }}
                    </div>
                @endif
            </div>
        </div>

        <div class="content">
            <div class="title-row clearfix">
                <div class="title-left">
                    <h1 class="invoice-title">INVOICE</h1>
                    <div class="meta-label">Invoice Number</div>
                    <div class="meta-value">{{ $invoice->invoice_number }}</div>
                </div>

                <div class="title-right">
                    <div class="meta-label">Date</div>
                    <div class="meta-value">{{ optional($invoice->billing_date)->format('d/m/Y') ?? '—' }}</div>
                </div>
            </div>

            <div class="company-summary">
                <strong>{{ $company['company_name'] ?? 'Company' }}</strong><br>
                {{ $company['company_email'] ?? '' }}<br>
                {{ $company['company_phone'] ?? '' }}<br>
                {{ $company['company_address'] ?? '' }}
            </div>

            <div class="divider"></div>

            <div class="section-title">Invoice Summary</div>

            <table class="items">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th class="right">Price</th>
                        <th class="right">Quantity</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $invoice->customer_name ?? 'N/A' }}</td>
                        <td>{{ $invoice->item ?? 'Service' }}</td>
                        <td>{{ $invoice->description ?? 'Service Invoice' }}</td>
                        <td class="right">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                        <td class="right">{{ $invoice->quantity ?? 1 }}</td>
                        <td class="right" style="color:#4B49AC; font-weight:700;">
                            {{ $invoice->currency }} {{ number_format(($invoice->amount * ($invoice->quantity ?? 1)), 2) }}
                        </td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="5" class="total-label">Total</td>
                        <td class="total-value">
                            {{ $invoice->currency }} {{ number_format(($invoice->amount * ($invoice->quantity ?? 1)), 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="info-grid clearfix">
                <div class="info-box">
                    <div class="section-title" style="color:#111827;">Billed To</div>
                    <div class="info-card">
                        <strong>{{ $invoice->customer_name }}</strong><br>
                        {{ $invoice->customer_email ?? 'No email provided' }}
                    </div>
                </div>

                <div class="info-box right-box">
                    <div class="section-title" style="color:#111827;">Payment Details</div>
                    <div class="info-card">
                        <strong>Status:</strong> {{ $invoice->status }}<br>
                        <strong>Due Date:</strong> {{ optional($invoice->billing_date)->format('d/m/Y') ?? '—' }}<br>
                        <strong>Reference:</strong> {{ $invoice->invoice_number }}
                    </div>
                </div>
            </div>
        </div>

        <div class="footer clearfix">
            <div class="footer-left">
                <div class="footer-brand">
                    @if(!empty($company['company_name']))
                        <span class="footer-brand-white">{{ $company['company_name'] }}</span>
                    @else
                        <span class="footer-brand-white">Fishi</span><span class="footer-brand-orange">Fox</span>
                    @endif
                </div>

                @if(!empty($company['invoice_footer']))
                    <div class="footer-text">
                        {{ $company['invoice_footer'] }}
                    </div>
                @else
                    <div class="footer-sub">Generated Invoice</div>
                @endif
            </div>

            <div class="footer-right">
                {{ $company['company_name'] ?? 'Company' }}<br>
                {{ $company['company_address'] ?? '' }}<br>
                {{ $company['company_email'] ?? '' }}<br>
                {{ $company['company_phone'] ?? '' }}<br>
                Generated on {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</body>
</html>