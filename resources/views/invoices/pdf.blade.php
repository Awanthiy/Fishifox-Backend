<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 30px;
        }
        .title {
            font-size: 26px;
            font-weight: bold;
            color: #4B49AC;
        }
        .section {
            margin-bottom: 20px;
        }
        .label {
            font-weight: bold;
            width: 160px;
            display: inline-block;
        }
        .box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
        }
        .amount {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }
        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">INVOICE</div>
        <div class="muted">FishiFox</div>
    </div>

    <div class="section">
        <div><span class="label">Invoice Number:</span> {{ $invoice->invoice_number }}</div>
        <div><span class="label">Customer Name:</span> {{ $invoice->customer_name }}</div>
        <div><span class="label">Customer Email:</span> {{ $invoice->customer_email ?? '—' }}</div>
        <div><span class="label">Billing Date:</span> {{ optional($invoice->billing_date)->format('Y-m-d') ?? '—' }}</div>
        <div><span class="label">Status:</span> {{ $invoice->status }}</div>
    </div>

    <div class="box">
        <div class="muted">Total Amount</div>
        <div class="amount">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</div>
    </div>

    <div style="margin-top: 40px;" class="muted">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>