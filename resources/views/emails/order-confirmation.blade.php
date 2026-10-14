<h2>Thank you for your order, {{ $order->name }}!</h2>
<p>Your order <strong>#{{ $order->id }}</strong> has been placed successfully and is currently being processed.</p>

<h3>Order Summary:</h3>
<ul>
    @foreach($order->items as $item)
        <li>{{ $item->product->name ?? 'Product' }} (x{{ $item->quantity }}) - ₹{{ number_format($item->price * $item->quantity, 2) }}</li>
    @endforeach
</ul>

<p><strong>Total Amount:</strong> ₹{{ number_format($order->total, 2) }}</p>
<p><strong>Shipping Address:</strong> {!! nl2br(e($order->address)) !!}</p>

<p>We will notify you once your order is dispatched with your India Post tracking number.</p>
<p><strong>NDR Spices</strong></p>