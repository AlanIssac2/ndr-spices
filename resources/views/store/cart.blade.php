<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shopping Cart - NDR Spices</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-100 min-h-screen bg-neutral-950 relative overflow-x-hidden pb-24">

    <!-- Immersive Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat scale-105 pointer-events-none" 
         style="background-image: url('{{ asset('images/plantation-bg.jpg') }}'); filter: brightness(0.25) contrast(1.1) blur(4px);">
    </div>
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-emerald-950/40 via-black/50 to-black/80 pointer-events-none"></div>

    <div class="relative z-10 max-w-lg mx-auto md:max-w-3xl px-4 pt-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-emerald-900/40 mb-6">
            <a href="{{ route('home') }}" class="text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1.5 text-sm font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Continue Shopping</span>
            </a>
            <h1 class="text-lg md:text-xl font-black tracking-tight text-emerald-200">Your Cart</h1>
        </div>

        @php
            $subtotal = $cartItems->sum(function($item) {
                $variant = $item->product->variants->where('id', $item->variant_id)->first();
                return $variant ? ($variant->price * $item->quantity) : 0;
            });
            $shipping = $subtotal > 0 ? 50 : 0; // Flat shipping rate example
            $grandTotal = $subtotal + ($subtotal > 0 ? $shipping : 0);
        @endphp

        @if($cartItems->count() > 0)
            <!-- Cart Items List -->
            <div class="space-y-3 mb-8">
                @foreach($cartItems as $item)
                    @php
                        $variant = $item->product->variants->where('id', $item->variant_id)->first();
                        $price = $variant ? $variant->price : 0;
                        $itemTotal = $price * $item->quantity;
                    @endphp

                    <div class="bg-[#1b261f]/85 backdrop-blur-md rounded-2xl border border-[#2b3a30] p-4 flex items-center justify-between gap-4 shadow-xl">
                        <!-- Product Info -->
                        <div class="flex items-center gap-3.5">
                            <div class="w-16 h-16 rounded-xl bg-black/40 overflow-hidden shrink-0 border border-emerald-900/40">
    @php
        $imgPath = $item->product->image_path;
        if(!$imgPath && !empty($item->product->media_paths)) {
            $decoded = is_array($item->product->media_paths) ? $item->product->media_paths : json_decode($item->product->media_paths, true);
            $imgPath = is_array($decoded) && count($decoded) > 0 ? $decoded[0] : null;
        }
    @endphp

    @if($imgPath)
        <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
    @else
        <div class="w-full h-full flex items-center justify-center text-[10px] text-gray-500 font-semibold">No Image</div>
    @endif
</div>
                            <div>
                                <h3 class="text-sm md:text-base font-extrabold text-white capitalize">{{ $item->product->name }}</h3>
                                <p class="text-xs text-emerald-400 font-semibold mt-0.5">{{ $variant?->weight_label ?? 'Standard' }}</p>
                                <p class="text-xs font-bold text-gray-300 mt-1">₹{{ number_format($price, 0) }} each</p>
                            </div>
                        </div>

                        <!-- Quantity Adjuster & Total -->
                        <div class="flex flex-col items-end gap-2">
                            <span class="text-sm md:text-base font-black text-emerald-300">₹{{ number_format($itemTotal, 0) }}</span>
                            
                            <!-- Quantity Form Controls -->
                            <form action="{{ route('cart.add') }}" method="POST" class="flex items-center bg-emerald-950/80 border border-emerald-600/50 rounded-xl overflow-hidden shadow-inner m-0">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <input type="hidden" name="variant_id" value="{{ $item->variant_id }}">
                                <input type="hidden" name="decrease" value="1">

                                <button type="submit" class="px-2.5 py-1 text-emerald-300 hover:bg-emerald-900 transition font-bold text-xs cursor-pointer">-</button>
                                <span class="px-2 text-xs font-black text-white">{{ $item->quantity }}</span>
                                
                                <button type="submit" formaction="{{ route('cart.add') }}" formmethod="POST" onclick="this.form.decrease.value='0'" class="px-2.5 py-1 text-emerald-300 hover:bg-emerald-900 transition font-bold text-xs cursor-pointer">+</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bill Summary Card -->
            <div class="bg-[#121c15]/90 backdrop-blur-xl border border-emerald-900/60 rounded-2xl p-5 shadow-2xl mb-8 space-y-3">
                <h3 class="text-sm font-extrabold text-emerald-200 uppercase tracking-wider mb-2">Order Summary</h3>
                
                <div class="flex justify-between text-sm text-gray-300">
                    <span>Bag Subtotal</span>
                    <span class="font-bold text-white">₹{{ number_format($subtotal, 0) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-300">
                    <span>Shipping Charges</span>
                    <span class="font-bold text-emerald-400">₹{{ number_format($shipping, 0) }}</span>
                </div>
                <div class="border-t border-emerald-900/50 pt-3 flex justify-between text-base font-black text-white">
                    <span>Grand Total</span>
                    <span class="text-emerald-300 text-lg">₹{{ number_format($grandTotal, 0) }}</span>
                </div>
            </div>

            <!-- Checkout Action Button -->
            <a href="{{ route('checkout.index') }}" class="w-full bg-emerald-500 hover:bg-emerald-400 text-black py-4 rounded-2xl font-black text-center tracking-wide shadow-2xl transition block text-base active:scale-98">
                Proceed to Checkout
            </a>

        @else
            <!-- Empty Cart State -->
            <div class="text-center py-20 bg-[#1b261f]/50 backdrop-blur-md rounded-3xl border border-emerald-900/30 p-8 shadow-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-emerald-700/60 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="text-lg font-black text-white mb-1">Your Cart is Empty</h3>
                <p class="text-xs text-gray-400 mb-6">Explore our premium collection of authentic spices and add them to your cart.</p>
                <a href="{{ route('home') }}" class="bg-emerald-500 hover:bg-emerald-400 text-black font-black px-6 py-3 rounded-xl text-sm shadow-lg transition">
                    Start Shopping
                </a>
            </div>
        @endif

    </div>

</body>
</html>