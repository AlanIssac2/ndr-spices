<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - NDR Spices</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased text-gray-100 min-h-screen bg-neutral-950 p-4 sm:p-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-neutral-800">
            <h1 class="text-2xl font-bold tracking-tight text-white">My Order History</h1>
            <a href="/" class="text-sm text-emerald-400 hover:underline">&larr; Back to Shop</a>
        </div>

        @if(isset($selectedOrder))
            <!-- Detailed View of Selected Order -->
            <div class="mb-4">
                <a href="{{ route('customer.orders') }}" class="text-sm text-emerald-400 hover:underline">&larr; Return to all orders</a>
            </div>

            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6 shadow-xl space-y-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-bold text-white">Order #{{ $selectedOrder->id }}</h2>
                        <p class="text-xs text-neutral-400 mt-1">Placed on {{ $selectedOrder->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        @php
                            $statusClasses = match($selectedOrder->status) {
                                'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'dispatched' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
                                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                default => 'bg-neutral-800 text-neutral-300 border-neutral-700'
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-semibold uppercase tracking-wider rounded-full border {{ $statusClasses }}">
                            {{ $selectedOrder->status }}
                        </span>
                    </div>
                </div>

                <!-- Ordered Items List (Side by Side) -->
                <div class="border-t border-neutral-800 pt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-3">Items Ordered</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($selectedOrder->items as $item)
                            <div class="bg-neutral-950 border border-neutral-800 p-3 rounded-lg flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $item->product->name ?? 'Product' }}</p>
                                    <p class="text-xs text-neutral-400">Qty: {{ $item->quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-emerald-400">₹{{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-neutral-800 pt-4 space-y-2 text-sm">
                    @if($selectedOrder->status === 'dispatched' || $selectedOrder->status === 'completed')
                        <p class="text-neutral-300"><strong class="text-white">Dispatched Date:</strong> {{ $selectedOrder->updated_at->format('d M Y, h:i A') }}</p>
                    @endif
                    <p class="text-neutral-300"><strong class="text-white">Shipping Address:</strong><br>{!! nl2br(e($selectedOrder->address)) !!}</p>
                    <p class="text-neutral-300"><strong class="text-white">Total Amount:</strong> ₹{{ number_format($selectedOrder->total, 2) }}</p>
                </div>

                @if($selectedOrder->tracking_number)
                    <div class="mt-6 bg-neutral-950 border border-emerald-500/30 rounded-lg p-4 space-y-2">
                        <h3 class="text-sm font-semibold text-emerald-400 uppercase tracking-wider">India Post Tracking</h3>
                        <p class="text-lg font-mono font-bold text-white tracking-widest">{{ $selectedOrder->tracking_number }}</p>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            You can check detailed, real-time shipping milestones on the official 
                            <a href="https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx" target="_blank" class="text-emerald-400 underline font-medium hover:text-emerald-300">India Post Tracking Portal</a> 
                            using the tracking ID provided above.
                        </p>
                    </div>
                @else
                    <div class="mt-6 bg-neutral-950 border border-neutral-800 rounded-lg p-4 text-xs text-neutral-400 italic text-neutral-400">
                        India Post tracking number will appear here once your order is dispatched.
                    </div>
                @endif
            </div>

        @else
            <!-- Order List -->
            @if($orders->isNotEmpty())
                <div class="space-y-4">
                    @foreach($orders as $ord)
                        @php
                            $badgeColor = match($ord->status) {
                                'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'dispatched' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
                                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                default => 'bg-neutral-800 text-neutral-300 border-neutral-700'
                            };
                        @endphp
                        <a href="{{ route('customer.orders', ['order_id' => $ord->id]) }}" 
                           class="block bg-neutral-900 border border-neutral-800 hover:border-emerald-500/50 rounded-xl p-5 transition-all duration-200 shadow-sm hover:shadow-md">
                            
                            <!-- Order Header info -->
                            <div class="flex items-center justify-between pb-3 border-b border-neutral-800 mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-white">Order #{{ $ord->id }}</h3>
                                    <p class="text-xs text-neutral-400 mt-0.5">Placed: {{ $ord->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                                <div class="text-right flex items-center space-x-3">
                                    <span class="text-sm font-bold text-white">₹{{ number_format($ord->total, 2) }}</span>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold uppercase rounded-full border {{ $badgeColor }}">
                                        {{ $ord->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Ordered Items Side by Side at the Top of Order Card -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($ord->items as $item)
                                    <div class="bg-neutral-950/60 border border-neutral-800/80 px-3 py-2 rounded-lg flex justify-between items-center">
                                        <span class="text-xs text-neutral-300 truncate max-w-[150px]">{{ $item->product->name ?? 'Product' }}</span>
                                        <span class="text-xs text-neutral-400 font-medium">x{{ $item->quantity }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-neutral-900 border border-neutral-800 rounded-xl">
                    <p class="text-neutral-400 text-sm">You haven't placed any orders yet.</p>
                </div>
            @endif
        @endif
    </div>
</body>
</html>