<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans; }
    </style>
</head>
<body>

<h1>INVOICE</h1>

<p><strong>Invoice:</strong> {{ $invoice->invoice_number }}</p>
<p><strong>Customer:</strong> {{ $invoice->customer_name }}</p>
<p><strong>Email:</strong> {{ $invoice->customer_email }}</p>
<p><strong>Amount:</strong> {{ $invoice->currency }} {{ $invoice->amount }}</p>
<p><strong>Status:</strong> {{ $invoice->status }}</p>

</body>
</html>