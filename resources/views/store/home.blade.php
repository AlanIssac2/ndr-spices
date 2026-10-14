<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NDR Spices</title>
    
    <!-- Favicon using your circular logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/ndr-logo.png') }}">

    <!-- Vite CSS and JS assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-100 min-h-screen bg-neutral-950 relative overflow-x-hidden pb-32">

    <!-- Immersive Drone-Shot Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat scale-105 pointer-events-none" 
         style="background-image: url('{{ asset('images/plantation-bg.jpg') }}'); filter: brightness(0.25) contrast(1.1) blur(4px);">
    </div>
    
    <!-- Subtle overlay to ensure dark rich contrast -->
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-emerald-950/40 via-black/50 to-black/80 pointer-events-none"></div>

    <!-- Main Container with Global Alpine State -->
    @php
        $initialCartItems = auth()->check() ? \App\Models\CartItem::with('product', 'product.variants')->where('user_id', auth()->id())->get() : collect();
        $initialCartCount = $initialCartItems->sum('quantity');
        $initialCartTotal = $initialCartItems->sum(function($item) {
            $v = $item->product->variants->where('id', $item->variant_id)->first();
            return $v ? ($v->price * $item->quantity) : 0;
        });
    @endphp

    <div class="relative z-10 max-w-lg mx-auto md:max-w-4xl px-4" 
         x-data="{ 
             search: '', 
             globalCartCount: {{ $initialCartCount }}, 
             globalCartTotal: {{ $initialCartTotal }} 
         }"
    >
        
       <!-- Mobile App Header -->
<header x-data="{ searchOpen: false, profileDropdown: false }" class="bg-black/45 backdrop-blur-md sticky top-0 z-50 border-b border-emerald-900/30 rounded-b-2xl">
    <div class="px-3 py-4 flex justify-between items-center">
        <a href="{{ route('home') }}" class="flex items-center space-x-2">
    <img src="{{ asset('images/ndr-logo.png') }}" alt="NDR Spices Logo" class="h-8 w-8 rounded-full object-cover border border-emerald-500/30">
    <span class="text-xl font-bold text-white tracking-wide">NDR Spices</span>
</a>
        
        <div class="flex items-center gap-3">
            
            <!-- Search Toggle Button -->
            <button @click="searchOpen = !searchOpen" class="text-gray-200 hover:text-emerald-400 transition cursor-pointer p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>

            <!-- Cart Icon with Live Badge -->
            <a href="{{ route('cart.index') }}" class="relative text-gray-200 hover:text-emerald-300 transition cursor-pointer p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span class="absolute -top-1 -right-1 bg-red-600 border-2 border-neutral-900 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full shadow" x-text="globalCartCount"></span>
            </a>

            <!-- Profile Dropdown Menu -->
            @auth
                <div class="relative" @click.away="profileDropdown = false">
                    <button @click="profileDropdown = !profileDropdown" class="text-gray-200 hover:text-emerald-400 transition cursor-pointer flex items-center bg-[#121c15] border border-emerald-900/50 p-2 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </button>

                    <div x-show="profileDropdown" 
                         x-transition.origin.top.right 
                         class="absolute right-0 mt-2 w-48 bg-[#121c15] border border-emerald-900/60 rounded-2xl shadow-2xl py-2 z-50 text-white" 
                         style="display: none;">
                        
                        <div class="px-4 py-2 border-b border-emerald-900/40">
                            <span class="block text-xs text-gray-400">Signed in as</span>
                            <span class="block text-sm font-bold text-emerald-300 truncate">{{ auth()->user()->name }}</span>
                        </div>

                        <a href="{{ route('customer.orders') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-neutral-800">
                            My Orders
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="m-0 border-t border-emerald-900/40 mt-1 pt-1">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-emerald-900/40 transition font-bold cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-sm font-bold text-emerald-400 hover:text-emerald-300 transition">
                    Login
                </a>
            @endauth

        </div>
    </div>

    <!-- Expandable Live Search Bar -->
    <div x-show="searchOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="px-4 pb-4" 
         style="display: none;">
        
        <div class="relative">
            <input type="text" 
                   name="search" 
                   id="search" 
                   x-model="search" 
                   placeholder="Search premium spices instantly..." 
                   class="w-full bg-[#121c15]/80 border border-emerald-900/50 rounded-xl pl-10 pr-4 py-2.5 text-sm text-emerald-100 placeholder-emerald-700/70 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 shadow-inner transition">
            
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-3 h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>
</header>

        <!-- Main Content -->
        <main class="pt-6">
            <!-- Banner Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-neutral-800 bg-neutral-900">
                    <img src="{{ asset('images/home-banner.png') }}" alt="Pure Spices, Naturally Better - NDR Spices" class="w-full h-auto object-cover max-h-[700px]">
                </div>
            </div>

            <h2 class="text-xl font-black mb-5 tracking-wide text-emerald-200/90 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Premium Spices
            </h2>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5 pb-12">
              @forelse ($products as $product)
                    @php
                        $userCartItems = auth()->check() ? \App\Models\CartItem::where('user_id', auth()->id())->where('product_id', $product->id)->get() : collect();
                    @endphp

                    <div x-data="{ 
                            selectedVariantId: {{ $product->variants->first()?->id ?? 'null' }},
                            selectedPrice: {{ $product->variants->first()?->price ?? 0 }},
                            cartItems: {{ json_encode($userCartItems->mapWithKeys(function($item) { return [$item->variant_id => $item->quantity]; })) }},
                            
                            updateCart(decrease = false) {
                                let token = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');

                                fetch('{{ route('cart.add') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': token,
                                        'Accept': 'application/json'
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        product_id: {{ $product->id }},
                                        variant_id: this.selectedVariantId,
                                        decrease: decrease ? 1 : 0
                                    })
                                })
                                .then(res => {
                                    if (res.status === 401) {
                                        window.location.href = '{{ route('login') }}';
                                        return;
                                    }
                                    return res.json();
                                })
                                .then(data => {
                                    if(data && data.success) {
                                        let oldQty = this.cartItems[this.selectedVariantId] || 0;
                                        let diff = data.quantity - oldQty;

                                        if(data.quantity > 0) {
                                            this.cartItems[this.selectedVariantId] = data.quantity;
                                        } else {
                                            delete this.cartItems[this.selectedVariantId];
                                        }

                                        this.globalCartCount += diff;
                                        this.globalCartTotal += (diff * this.selectedPrice);
                                    }
                                })
                                .catch(err => console.error('Cart update failed:', err));
                            }
                         }"
                         x-show="search === '' || '{{ strtolower($product->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($product->description) }}'.includes(search.toLowerCase())"
                         class="bg-[#1b261f]/85 backdrop-blur-md rounded-2xl overflow-hidden border border-[#2b3a30] hover:border-emerald-600/50 flex flex-col shadow-2xl transition-all duration-300 p-3"
                    >
                       
                       <!-- Swipeable Media Gallery -->
                        <div x-data="{
                                init() {
                                    setInterval(() => {
                                        let el = this.$refs.slider;
                                        if(!el) return;
                                        if (el.scrollLeft >= (el.scrollWidth - el.clientWidth - 10)) {
                                            el.scrollTo({ left: 0, behavior: 'smooth' });
                                        } else {
                                            el.scrollBy({ left: el.clientWidth, behavior: 'smooth' });
                                        }
                                    }, 4000); 
                                }
                            }"
                            class="h-32 xs:h-36 md:h-48 bg-black/40 rounded-xl w-xl relative overflow-hidden"
                        >
                            <div x-ref="slider" class="w-full h-full flex overflow-x-auto snap-x snap-mandatory hide-scroll">
                                @php
                                    $mediaPaths = is_array($product->media_paths) ? $product->media_paths : json_decode($product->media_paths, true);
                                    $imageGallery = [];
                                    if(!empty($mediaPaths) && is_array($mediaPaths)) {
                                        foreach($mediaPaths as $media) {
                                            if(!Str::endsWith($media, ['.mp4', '.mov'])) {
                                                $imageGallery[] = asset('storage/' . $media);
                                            }
                                        }
                                    } elseif(!empty($product->image_path)) {
                                        $imageGallery[] = asset('storage/' . $product->image_path);
                                    }
                                @endphp

                                @if(!empty($mediaPaths) && is_array($mediaPaths))
                                    @foreach($mediaPaths as $media)
                                        <div class="w-full h-full shrink-0 snap-center relative">
                                            @if(Str::endsWith($media, ['.mp4', '.mov']))
                                                <video src="{{ asset('storage/' . $media) }}" class="object-cover w-full h-full" autoplay muted loop playsinline></video>
                                            @else
                                                <img src="{{ asset('storage/' . $media) }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="object-cover w-full h-full cursor-zoom-in hover:brightness-110 transition duration-300"
                                                     @click="$dispatch('open-lightbox', { current: '{{ asset('storage/' . $media) }}', gallery: {{ json_encode($imageGallery) }} })">
                                            @endif
                                        </div>
                                    @endforeach
                                @elseif(!empty($product->image_path))
                                    <div class="w-full h-full shrink-0 snap-center relative">
                                        <img src="{{ asset('storage/' . $product->image_path) }}" 
                                             alt="{{ $product->name }}" 
                                             class="object-cover w-full h-full cursor-zoom-in hover:brightness-110 transition duration-300"
                                             @click="$dispatch('open-lightbox', { current: '{{ asset('storage/' . $product->image_path) }}', gallery: {{ json_encode($imageGallery) }} })">
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-500 shrink-0 snap-center">
                                        <span>No Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Product Details & Description -->
                        <div class="pt-2.5 grow flex flex-col">
                            <h3 class="text-sm md:text-base font-extrabold tracking-wide text-gray-100 truncate capitalize">{{ $product->name }}</h3>
                            <p class="text-[11px] md:text-xs text-emerald-400/80 mt-0.5 line-clamp-1 font-medium">{{ $product->description }}</p>

                            <!-- Side-by-Side Swipeable Variant Boxes -->
                            <div class="mt-2.5">
                                @if($product->variants->count() > 0)
                                    <div class="flex gap-2 overflow-x-auto pb-1 snap-x hide-scroll">
                                        @foreach($product->variants as $variant)
                                            <button type="button" 
                                                    @click="selectedVariantId = {{ $variant->id }}; selectedPrice = {{ $variant->price }}"
                                                    :class="selectedVariantId === {{ $variant->id }} ? 'bg-emerald-600/40 border-emerald-400 text-white shadow-md' : 'bg-[#121c15]/60 border-emerald-950 text-gray-400 hover:border-emerald-800'"
                                                    class="shrink-0 snap-start px-3 py-1.5 rounded-xl border text-center transition cursor-pointer flex flex-col items-center min-w-[65px]"
                                            >
                                                <span class="text-[10px] md:text-xs font-bold">{{ $variant->weight_label }}</span>
                                                <span class="text-[11px] md:text-xs font-black text-emerald-300 mt-0.5">₹{{ number_format($variant->price, 0) }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-[11px] text-red-400 italic font-semibold mt-1">Out of Stock</p>
                                @endif
                            </div>

                            <!-- Bottom Action Area -->
                            <div class="mt-3 pt-2 border-t border-emerald-900/30 flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Price</span>
                                    <span class="text-sm md:text-base font-black text-emerald-400" x-text="'₹' + selectedPrice"></span>
                                </div>

                                @if($product->variants->count() > 0)
                                    <!-- Add Button -->
                                    <button type="button" 
                                            @click="updateCart(false)" 
                                            x-show="!cartItems[selectedVariantId]"
                                            class="bg-emerald-500 hover:bg-emerald-400 text-black px-4 py-2 rounded-xl text-xs md:text-sm font-black tracking-wide shadow-lg active:scale-95 transition-all cursor-pointer flex items-center gap-1.5">
                                        <span>Add to Cart</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>

                                    <!-- Quantity Adjuster (- 1 +) -->
                                    <div class="flex items-center bg-emerald-950/80 border border-emerald-600/50 rounded-xl overflow-hidden shadow-inner" x-show="cartItems[selectedVariantId]" style="display: none;">
                                        <button type="button" @click="updateCart(true)" class="px-2.5 py-1.5 text-emerald-300 hover:bg-emerald-900 transition font-bold text-xs cursor-pointer">
                                            -
                                        </button>
                                        <span class="px-2 text-xs md:text-sm font-black text-white" x-text="cartItems[selectedVariantId] || 0"></span>
                                        <button type="button" @click="updateCart(false)" class="px-2.5 py-1.5 text-emerald-300 hover:bg-emerald-900 transition font-bold text-xs cursor-pointer">
                                            +
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-400 text-sm font-semibold">No spices available right now. Check back soon!</p>
                    </div>
                @endforelse
            </div>
        </main>

        <!-- Reactive Floating Cart Bar -->
        @auth
            <div class="fixed bottom-4 left-4 right-4 z-40 max-w-lg mx-auto md:max-w-md"
                 x-show="globalCartCount > 0"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-6"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-6"
                 style="display: none;"
            >
                <div class="bg-emerald-950/95 backdrop-blur-xl border border-emerald-600/60 rounded-2xl p-4 shadow-2xl flex items-center justify-between text-white">
                    <div class="flex flex-col">
                        <span class="text-xs text-emerald-400 font-semibold">
                            <span x-text="globalCartCount"></span> <span x-text="globalCartCount === 1 ? 'Item' : 'Items'"></span> in Cart
                        </span>
                        <span class="text-base font-black text-emerald-200" x-text="'₹' + globalCartTotal.toLocaleString('en-IN')"></span>
                    </div>

                    <a href="{{ route('cart.index') }}" class="bg-emerald-500 hover:bg-emerald-400 text-black px-5 py-2.5 rounded-xl text-sm font-black tracking-wide shadow-lg transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                        <span>View Cart</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @endauth

        <!-- Footer -->
        <footer class="bg-neutral-950 border-t border-neutral-800 text-neutral-400 py-12 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                
                <!-- Brand Info -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <span class="text-xl font-bold text-white tracking-wide">NDR Spices</span>
                    

                <!-- Quick Links -->
                <div class="flex flex-col space-y-2 text-sm">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-white">Quick Links</h4>
                    <a href="{{ route('home') }}" class="hover:text-emerald-400 transition">Shop Store</a>
                    <a href="{{ route('customer.orders') }}" class="hover:text-emerald-400 transition">My Orders</a>
                    <a href="{{ route('track.order') }}" class="hover:text-emerald-400 transition">Track Shipment</a>
                </div>

                <!-- Contact & Copyright -->
                <div class="space-y-2 text-sm md:text-right">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-white">Authentic & Natural</h4>
                    <p class="text-xs text-neutral-400">100% Pure Spices, Naturally Better.</p>
                    <p class="text-xs text-neutral-500 pt-2">&copy; {{ date('Y') }} NDR Spices. All rights reserved.</p>
                </div>

            </div>
        </footer>
    </div>

    <!-- Full-Screen Image Zoom Modal -->
    <div x-data="{ 
            open: false, 
            imgSrc: '', 
            gallery: [], 
            currentIndex: 0,
            touchStartX: 0,
            touchEndX: 0,
            openLightbox(data) {
                this.open = true;
                this.imgSrc = data.current;
                this.gallery = data.gallery || [];
                this.currentIndex = this.gallery.indexOf(data.current);
            },
            nextImage() {
                if(this.gallery.length <= 1) return;
                this.currentIndex = (this.currentIndex + 1) % this.gallery.length;
                this.imgSrc = this.gallery[this.currentIndex];
            },
            prevImage() {
                if(this.gallery.length <= 1) return;
                this.currentIndex = (this.currentIndex - 1 + this.gallery.length) % this.gallery.length;
                this.imgSrc = this.gallery[this.currentIndex];
            },
            handleTouchStart(e) {
                this.touchStartX = e.changedTouches[0].screenX;
            },
            handleTouchEnd(e) {
                this.touchEndX = e.changedTouches[0].screenX;
                let diff = this.touchStartX - this.touchEndX;
                if (Math.abs(diff) > 50) { 
                    if (diff > 0) this.nextImage(); 
                    else this.prevImage(); 
                }
            }
         }"
         @open-lightbox.window="openLightbox($event.detail)"
         x-show="open"
         style="display: none;"
         x-transition.opacity.duration.250ms
         class="fixed inset-0 z-[100] bg-black/95 flex flex-col items-center justify-center p-4 backdrop-blur-sm"
    >
        <div @click.away="open = false" class="max-w-3xl w-full flex flex-col relative">
            <div class="w-full flex justify-end pb-3">
                <button @click="open = false" 
                        class="text-white bg-red-600 hover:bg-red-700 active:scale-95 px-4 py-2 rounded-full flex items-center gap-2 text-sm font-black shadow-lg border border-red-500 cursor-pointer transition">
                    <span>Close</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div @touchstart="handleTouchStart" @touchend="handleTouchEnd" class="relative w-full h-[60vh] md:h-[75vh] bg-neutral-900 rounded-2xl border-2 border-neutral-800 flex items-center justify-center shadow-2xl select-none">
                <img :src="imgSrc" class="max-w-full max-h-full object-contain pointer-events-none rounded-xl" alt="Zoomed spice">
            </div>
        </div>
        
    </div>
    <div class="flex md:justify-end items-center gap-3 pt-1">
                <!-- WhatsApp -->
                <a href="https://wa.me/91XXXXXXXXXX" target="_blank" class="bg-neutral-900 border border-neutral-800 p-2 rounded-xl text-emerald-400 hover:bg-emerald-950 hover:border-emerald-600 transition" title="WhatsApp">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                </a>

                <!-- Facebook -->
                <a href="https://facebook.com/ndrspices" target="_blank" class="bg-neutral-900 border border-neutral-800 p-2 rounded-xl text-emerald-400 hover:bg-emerald-950 hover:border-emerald-600 transition" title="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.373 14.5 5 15.5 5H18V0h-3.808C10.59 0 9 1.581 9 4.615V8z"/>
                    </svg>
                </a>

                <!-- Instagram -->
                <a href="https://instagram.com/ndrspices" target="_blank" class="bg-neutral-900 border border-neutral-800 p-2 rounded-xl text-emerald-400 hover:bg-emerald-950 hover:border-emerald-600 transition" title="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
            </div>

            <p class="text-xs text-neutral-500 pt-2">&copy; {{ date('Y') }} NDR Spices. All rights reserved.</p>
        </div>
</body>
</html>