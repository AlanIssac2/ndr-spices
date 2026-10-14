<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Order Sheets - NDR Spices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .invoice-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            box-sizing: border-box;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .invoice-page:last-child {
            page-break-after: avoid;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .brand h2 { margin: 0; font-size: 24px; color: #1b261f; }
        .brand p { margin: 2px 0 0; font-size: 12px; color: #555; }
        .order-meta { text-align: right; font-size: 13px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 13px;
            text-align: left;
        }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        
        .totals {
            width: 40%;
            margin-left: auto;
            margin-bottom: 40px;
        }
        .totals td { padding: 6px 10px; }

        .shipping-label {
            border: 2px dashed #333;
            padding: 20px;
            border-radius: 8px;
            background: #fafafa;
            margin-top: auto;
        }
        .shipping-label h4 { margin: 0 0 10px; font-size: 14px; text-transform: uppercase; color: #333; }
        .shipping-label p { margin: 4px 0; font-size: 14px; font-weight: bold; }
        
        @media print {
            body { background: transparent; }
        }
    </style>
</head>
<body onload="window.print()">

    @foreach($orders as $order)
        <div class="invoice-page">
            <div>
                <!-- Header -->
                <div class="header">
                    <div class="brand">
                        <div style="text-align: left; margin-bottom: 2px; margin-top: -50px;">
                            <img src="{{ asset('images/ndr-logo.png') }}" alt="NDR Spices" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 2px solid #1b261f;">
                            <h2 style="margin: 5px 0 0; color: #1b261f; font-family: Arial, sans-serif;">NDR Spices - Order Invoice</h2>
                        </div>
                        <p>Pure & Authentic Indian Spices</p>
                    </div>
                    <div class="order-meta">
                        <strong>Order ID:</strong> #{{ $order->id }}<br>
                        <strong>Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}<br>
                        <strong>Payment:</strong> {{ strtoupper($order->payment_method) }}<br>
                        @if($order->tracking_number)
                            <strong>Tracking:</strong> {{ $order->tracking_number }}
                        @endif
                    </div>
                </div>

                <!-- Items Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'Spice Product' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-right">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals -->
                <table class="totals">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-right">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td class="text-right">₹{{ number_format($order->shipping, 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold; background: #eee;">
                        <td>Total Payable:</td>
                        <td class="text-right">₹{{ number_format($order->total, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- Customer Shipping Address Card at the Bottom -->
            <div class="shipping-label">
                <h3>TO:</h3>
                <p style="font-size: 16px;">{{ $order->name }}</p>
                <p style="font-weight: normal; font-size: 14px;">Phone: {{ $order->phone }}</p>
                <p style="font-weight: normal; font-size: 14px; line-height: 1.5; margin-top: 6px;">
                    {!! nl2br(e($order->address)) !!}
                </p>
            </div>
        </div>
    @endforeach

</body>
</html>