<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Your Order - NDR Spices</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f4; margin: 0; padding: 40px; color: #333; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h2 { color: #1b261f; margin-top: 0; text-align: center; }
        form { display: flex; gap: 10px; margin-bottom: 25px; }
        input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        button { padding: 12px 24px; background: #1b261f; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #2e4034; }
        
        .order-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
        .order-card-item { border: 1px solid #e0e0e0; padding: 15px 20px; border-radius: 6px; background: #fafafa; display: flex; justify-content: space-between; align-items: center; text-decoration: none; color: inherit; transition: 0.2s; }
        .order-card-item:hover { background: #f0f4f1; border-color: #1b261f; }
        
        .result-card { border: 1px solid #c8d6cb; padding: 25px; border-radius: 6px; background: #f9fbf9; margin-top: 20px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
        .badge.pending { background: #fff3cd; color: #856404; }
        .badge.dispatched { background: #cce5ff; color: #004085; }
        .badge.completed { background: #d4edda; color: #155724; }
        
        .tracking-box { background: #fff; border: 1px dashed #1b261f; padding: 15px; border-radius: 6px; margin-top: 15px; }
        .tracking-box h4 { margin: 0 0 8px; color: #1b261f; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #1b261f; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Track Your NDR Spices Orders</h2>
        
        <!-- Search Form -->
        <form action="{{ route('track.order') }}" method="GET">
            <input type="text" name="query" placeholder="Enter Phone Number or Tracking ID" value="{{ request('query') }}" required>
            <button type="submit">Search Orders</button>
        </form>

        @if(request()->has('query'))
            @php
                $searchQuery = request('query');
                // Fetch all orders matching phone, tracking number, or specific ID
                $orders = \App\Models\Order::where('phone', $searchQuery)
                    ->orWhere('tracking_number', $searchQuery)
                    ->orWhere('id', $searchQuery)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $selectedOrderId = request('order_id');
                $selectedOrder = $orders->firstWhere('id', $selectedOrderId) ?? ($orders->count() === 1 ? $orders->first() : null);
            @endphp

            @if($orders->isNotEmpty())
                @if($selectedOrder && (request()->has('order_id') || $orders->count() === 1))
                    <!-- Detailed View of Selected Order -->
                    <a href="{{ route('track.order', ['query' => $searchQuery]) }}" class="back-link">&larr; Back to all orders list</a>
                    <div class="result-card">
                        <h3>Order #{{ $selectedOrder->id }}</h3>
                        <p><strong>Customer Name:</strong> {{ $selectedOrder->name }}</p>
                        <p><strong>Status:</strong> <span class="badge {{ $selectedOrder->status }}">{{ strtoupper($selectedOrder->status) }}</span></p>
                        <p><strong>Order Date:</strong> {{ $selectedOrder->created_at->format('d M Y, h:i A') }}</p>
                        
                        @if($selectedOrder->status === 'dispatched' || $selectedOrder->status === 'completed')
                            <p><strong>Dispatched Date:</strong> {{ $selectedOrder->updated_at->format('d M Y, h:i A') }}</p>
                        @endif

                        <p><strong>Shipping Address:</strong> {!! nl2br(e($selectedOrder->address)) !!}</p>
                        <p><strong>Total Amount:</strong> ₹{{ number_format($selectedOrder->total, 2) }}</p>

                        @if($selectedOrder->tracking_number)
                            <div class="tracking-box">
                                <h4>India Post Tracking Details</h4>
                                <p><strong>Tracking Number:</strong> <span style="font-size: 16px; color: #004085; font-weight: bold;">{{ $selectedOrder->tracking_number }}</span></p>
                                <p style="font-size: 13px; color: #555; line-height: 1.5; margin-top: 8px;">
                                    <strong>Note:</strong> You can check detailed, real-time transit milestones on the official 
                                    <a href="https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx" target="_blank" style="color: #004085; font-weight: bold;">India Post Tracking Portal</a> 
                                    using the tracking ID provided above.
                                </p>
                            </div>
                        @else
                            <p style="color: #856404; margin-top: 15px;"><em>India Post tracking number will be updated here once your order is dispatched.</em></p>
                        @endif
                    </div>
                @else
                    <!-- List All Orders for this Phone / Customer -->
                    <p style="font-weight: bold; margin-bottom: 10px;">Found {{ $orders->count() }} order(s). Click an order to view full shipping details:</p>
                    <div class="order-list">
                        @foreach($orders as $ord)
                            <a href="{{ route('track.order', ['query' => $searchQuery, 'order_id' => $ord->id]) }}" class="order-card-item">
                                <div>
                                    <strong>Order #{{ $ord->id }}</strong><br>
                                    <small style="color: #666;">Date: {{ $ord->created_at->format('d M Y') }}</small>
                                </div>
                                <div>
                                    <span class="badge {{ $ord->status }}">{{ strtoupper($ord->status) }}</span>
                                    <span style="margin-left: 10px; font-weight: bold;">₹{{ number_format($ord->total, 2) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @else
                <p style="color: red; text-align: center;">No orders found matching "{{ $searchQuery }}". Please check your phone number or tracking ID.</p>
            @endif
        @endif
    </div>
</body>
</html>