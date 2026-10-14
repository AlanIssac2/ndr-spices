@php
    $subtotal = $cartItems->sum(function($item) {
        $variant = $item->product->variants->where('id', $item->variant_id)->first();
        return $variant ? ($variant->price * $item->quantity) : 0;
    });

    // Check if customer has placed at least one previous order
    $hasOrderedBefore = \App\Models\Order::where('user_id', auth()->id())->exists();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NDR Spices</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-100 min-h-screen bg-neutral-950 relative overflow-x-hidden pb-24"
      x-data="{ 
          subtotal: {{ $subtotal }}, 
          state: 'Kerala',
          get shipping() {
              return this.state.trim().toLowerCase() === 'kerala' ? 50 : 100;
          },
          get grandTotal() {
              return Number(this.subtotal) + Number(this.shipping);
          }
      }"
>

    <!-- Immersive Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat scale-105 pointer-events-none" 
         style="background-image: url('{{ asset('images/plantation-bg.jpg') }}'); filter: brightness(0.25) contrast(1.1) blur(4px);">
    </div>
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-emerald-950/40 via-black/50 to-black/80 pointer-events-none"></div>

    <div class="relative z-10 max-w-lg mx-auto md:max-w-2xl px-4 pt-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-emerald-900/40 mb-6">
            <a href="{{ route('cart.index') }}" class="text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1.5 text-sm font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Back to Cart</span>
            </a>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-emerald-200">Checkout</h1>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-950/80 border border-red-500 rounded-2xl p-4 text-red-200 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.place') }}" method="POST" class="space-y-6" @submit="console.log('Form submitting...')">
            @csrf

            <!-- Shipping Details Card -->
            <div class="bg-[#1b261f]/85 backdrop-blur-md rounded-2xl border border-[#2b3a30] p-5 shadow-xl space-y-4">
                <h3 class="text-sm font-extrabold text-emerald-300 uppercase tracking-wider">Shipping Details</h3>

                <div>
                    <label class="block text-xs font-bold text-gray-300 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ auth()->user()->name }}" required class="w-full bg-[#121c15] border border-emerald-900/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="Enter 10-digit mobile number" required class="w-full bg-[#121c15] border border-emerald-900/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-300 mb-1">Delivery Address</label>
                    <textarea name="address" rows="2" placeholder="House no, Street name, Landmark" required class="w-full bg-[#121c15] border border-emerald-900/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">Pincode</label>
                        <input type="text" name="pincode" placeholder="6-digit pincode" required class="w-full bg-[#121c15] border border-emerald-900/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">State</label>
                        <input type="text" 
                               name="state" 
                               x-model="state" 
                               placeholder="Enter state (e.g. Kerala)" 
                               required 
                               class="w-full bg-[#121c15] border border-emerald-900/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Payment Method Card -->
            <div class="bg-[#1b261f]/85 backdrop-blur-md rounded-2xl border border-[#2b3a30] p-5 shadow-xl space-y-3">
                <h3 class="text-sm font-extrabold text-emerald-300 uppercase tracking-wider">Payment Method</h3>
                
                @if($hasOrderedBefore)
                    <label class="flex items-center gap-3 p-3 bg-[#121c15] border border-emerald-900/60 rounded-xl cursor-pointer">
                        <input type="radio" name="payment_method" value="cod" checked class="text-emerald-500 focus:ring-emerald-500">
                        <div>
                            <span class="block text-sm font-bold text-white">Cash on Delivery (COD)</span>
                            <span class="block text-xs text-gray-400">Pay securely when your spices arrive</span>
                        </div>
                    </label>
                @else
                    <div class="p-3 bg-red-950/40 border border-red-900/50 rounded-xl">
                        <span class="block text-sm font-bold text-red-300">Cash on Delivery Not Available</span>
                        <span class="block text-xs text-gray-400 mt-0.5">COD is unlocked after your first successful order.</span>
                    </div>
                    <input type="hidden" name="payment_method" value="online">
                @endif
            </div>

            <!-- Dynamic Order Summary Card -->
            <div class="bg-[#121c15]/90 backdrop-blur-xl border border-emerald-900/60 rounded-2xl p-5 shadow-2xl space-y-3">
                <h3 class="text-sm font-extrabold text-emerald-200 uppercase tracking-wider">Summary</h3>
                <div class="flex justify-between text-sm text-gray-300">
                    <span>Subtotal</span>
                    <span class="font-bold text-white" x-text="'₹' + subtotal"></span>
                </div>
                <div class="flex justify-between text-sm text-gray-300">
                    <span>Shipping (<span x-text="state || 'State'"></span>)</span>
                    <span class="font-bold text-emerald-400" x-text="'₹' + shipping"></span>
                </div>
                <div class="border-t border-emerald-900/50 pt-3 flex justify-between text-base font-black text-white">
                    <span>Total Payable</span>
                    <span class="text-emerald-300 text-lg" x-text="'₹' + grandTotal"></span>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-black py-4 rounded-2xl font-black text-center tracking-wide shadow-2xl transition block text-base active:scale-98 cursor-pointer">
                Place Order Now
            </button>
        </form>
    </div>

</body>
</html>