<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estimate {{ $estimate->estimate_number }}</title>
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
    <h1>Estimate {{ $estimate->estimate_number }}</h1>
    <p class="muted">{{ $estimate->business->name }} · {{ $estimate->business->phone }} · {{ $estimate->business->email }}</p>
    <h2>Customer</h2>
    <p>{{ $estimate->customer->display_name }}<br>{{ $estimate->customer->full_address }}</p>
    <table>
        <thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Total</th></tr></thead>
        <tbody>
        @foreach($estimate->items as $item)
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
        <tr><td>Subtotal</td><td class="right">${{ number_format($estimate->subtotal_cents / 100, 2) }}</td></tr>
        <tr><td>Discount</td><td class="right">${{ number_format($estimate->discount_cents / 100, 2) }}</td></tr>
        <tr><td>Tax</td><td class="right">${{ number_format($estimate->tax_cents / 100, 2) }}</td></tr>
        <tr><th>Total</th><th class="right">${{ number_format($estimate->total_cents / 100, 2) }}</th></tr>
    </table>
    <p>{{ $estimate->notes }}</p>
    <p class="muted">{{ $estimate->terms }}</p>
</body>
</html>
