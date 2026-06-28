<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 32px; }
        h1, h2 { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
        .totals { margin-left: auto; width: 320px; }
        @media print { button { display: none; } body { margin: 0.5in; } }
    </style>
</head>
<body>
    <button onclick="window.print()">Print</button>
    <h1>Invoice {{ $invoice->invoice_number }}</h1>
    <p class="muted">{{ $invoice->business->name }} · {{ $invoice->business->phone }} · {{ $invoice->business->email }}</p>
    <h2>Customer</h2>
    <p>{{ $invoice->customer->display_name }}<br>{{ $invoice->customer->full_address }}</p>
    <p>Due date: {{ optional($invoice->due_date)->toFormattedDateString() }}</p>
    <table>
        <thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Total</th></tr></thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="right">{{ number_format((float) $item->quantity, 2) }}</td>
                <td class="right">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                <td class="right">${{ number_format($item->line_total_cents / 100, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="totals">
        <tr><td>Subtotal</td><td class="right">${{ number_format($invoice->subtotal_cents / 100, 2) }}</td></tr>
        <tr><td>Discount</td><td class="right">${{ number_format($invoice->discount_cents / 100, 2) }}</td></tr>
        <tr><td>Tax</td><td class="right">${{ number_format($invoice->tax_cents / 100, 2) }}</td></tr>
        <tr><td>Paid</td><td class="right">${{ number_format($invoice->amount_paid_cents / 100, 2) }}</td></tr>
        <tr><th>Balance Due</th><th class="right">${{ number_format($invoice->balance_due_cents / 100, 2) }}</th></tr>
    </table>
</body>
</html>
