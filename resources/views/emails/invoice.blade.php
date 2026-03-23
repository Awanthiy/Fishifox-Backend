<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Email</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2>Hello {{ $invoice->customer_name }},</h2>

    <p>Please find your invoice attached.</p>

    <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
    <p><strong>Amount:</strong> {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</p>
    <p><strong>Billing Date:</strong> {{ optional($invoice->billing_date)->format('Y-m-d') ?? '—' }}</p>
    <p><strong>Status:</strong> {{ $invoice->status }}</p>

    <p>Thank you.</p>
</body>
</html>