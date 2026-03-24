<!DOCTYPE html>
<html>
<body>
    <h2>Hello {{ $invoice->customer_name ?? ($invoice->customer->name ?? 'Customer') }}</h2>
    <p>{{ $messageText }}</p>
    <p>Your invoice is attached.</p>
</body>
</html>