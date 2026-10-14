<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - NDR Spices</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-100 min-h-screen bg-neutral-950 relative overflow-x-hidden flex items-center justify-center p-4">

    <!-- Immersive Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat scale-105 pointer-events-none" 
         style="background-image: url('{{ asset('images/plantation-bg.jpg') }}'); filter: brightness(0.25) contrast(1.1) blur(4px);">
    </div>
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-emerald-950/40 via-black/50 to-black/80 pointer-events-none"></div>

    <div class="relative z-10 max-w-md w-full bg-[#1b261f]/90 backdrop-blur-xl border border-emerald-600/50 rounded-3xl p-8 shadow-2xl text-center space-y-4">
        <div class="w-16 h-16 bg-emerald-500/20 border-2 border-emerald-400 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-black text-white">Order Placed Successfully!</h1>
        <p class="text-xs text-gray-300">Thank you for shopping with NDR Spices. Your order <span class="text-emerald-400 font-bold">#{{ $order->id }}</span> has been confirmed and is being processed.</p>

        <div class="bg-[#121c15] border border-emerald-900/60 rounded-xl p-4 text-left space-y-2 my-4">
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Total Amount:</span>
                <span class="text-emerald-300 font-black">₹{{ number_format($order->total, 0) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Payment:</span>
                <span class="text-white font-bold uppercase">{{ $order->payment_method }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Shipping to:</span>
                <span class="text-white truncate max-w-[180px]">{{ $order->address }}</span>
            </div>
        </div>

        <div class="space-y-2.5">
            <a href="{{ route('home') }}" class="w-full bg-emerald-500 hover:bg-emerald-400 text-black font-black py-3 rounded-xl text-sm shadow-lg transition block">
                Continue Shopping
            </a>
        </div>
    </div>

</body>
</html>