<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderConfirmationMail;
use App\Services\WhatsAppNotification;
use Illuminate\Support\Facades\Mail;


// 1. The Main NDR Spices Storefront with Live Search Support
Route::get('/', function (Request $request) {
    $search = $request->input('search');

    $products = Product::with('variants')
        ->where('is_active', true)
        ->when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
        })
        ->get();

    return view('store.home', compact('products'));
})->name('home');

// 2. Redirect the Breeze Dashboard back to the Store
Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. Asynchronous Add/Update Cart Route (No Refresh)
Route::post('/cart/add', function (Request $request) {
    $userId = auth()->id();
    $productId = $request->product_id;
    $variantId = $request->variant_id;

    $cartItem = CartItem::where('user_id', $userId)
                        ->where('product_id', $productId)
                        ->where('variant_id', $variantId)
                        ->first();

    if ($request->decrease) {
        if ($cartItem) {
            if ($cartItem->quantity > 1) {
                $cartItem->decrement('quantity');
                $cartItem->refresh();
            } else {
                $cartItem->delete();
                $cartItem = null;
            }
        }
    } else {
        if ($cartItem) {
            $cartItem->increment('quantity');
            $cartItem->refresh();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => 1,
            ]);
        }
    }

    $currentQty = $cartItem ? $cartItem->quantity : 0;

    // Return JSON response for asynchronous JavaScript fetches
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'quantity' => $currentQty
        ]);
    }

    return back();
})->middleware(['web', 'auth'])->name('cart.add');

// 4. Cart Page Index Route
Route::get('/cart', function () {
    $cartItems = CartItem::with('product', 'product.variants')
                         ->where('user_id', auth()->id())
                         ->get();
    return view('store.cart', compact('cartItems'));
})->middleware('auth')->name('cart.index');

// 5. Checkout Routes
Route::get('/checkout', function () {
    $cartItems = CartItem::with('product', 'product.variants')
                         ->where('user_id', auth()->id())
                         ->get();

    if ($cartItems->isEmpty()) {
        return redirect()->route('cart.index');
    }

    return view('store.checkout', compact('cartItems'));
})->middleware('auth')->name('checkout.index');

// Prevent 405 GET errors if page is refreshed or accessed directly
Route::get('/checkout/place-order', function () {
    return redirect()->route('checkout.index');
})->middleware('auth');

Route::post('/checkout/place-order', function (Request $request) {
    \Log::info('CHECKOUT ROUTE HIT! Request data: ', $request->all());
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:500',
        'state' => 'required|string|max:100',
        'pincode' => 'required|string|max:20',
        'payment_method' => 'required|string',
    ]);

    $userId = auth()->id();
    
    // Security check: If trying to use COD but never ordered before, reject it
    $hasOrderedBefore = Order::where('user_id', $userId)->exists();
    if ($request->payment_method === 'cod' && !$hasOrderedBefore) {
        return back()->withErrors(['payment_method' => 'Cash on Delivery is only available for returning customers.']);
    }

    $cartItems = CartItem::with('product.variants')->where('user_id', $userId)->get();

    if ($cartItems->isEmpty()) {
        return redirect()->route('cart.index');
    }

    $subtotal = $cartItems->sum(function($item) {
        $variant = $item->product->variants->where('id', $item->variant_id)->first();
        return $variant ? ($variant->price * $item->quantity) : 0;
    });

    $isKerala = strtolower(trim($request->state)) === 'kerala';
    $shipping = $isKerala ? 50 : 100;
    $grandTotal = $subtotal + $shipping;

    $order = Order::create([
        'user_id' => $userId,
        'name' => $request->name,
        'phone' => $request->phone,
        'address' => $request->address . ', ' . $request->state . ' - ' . $request->pincode,
        'payment_method' => $request->payment_method,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $grandTotal,
        'status' => 'pending',
    ]);

    foreach ($cartItems as $item) {
        $variant = $item->product->variants->where('id', $item->variant_id)->first();
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id,
            'quantity' => $item->quantity,
            'price' => $variant ? $variant->price : 0,
        ]);
    }

    CartItem::where('user_id', $userId)->delete();

    // Automatically send Email Confirmation
    try {
        if (auth()->user()->email) {
            Mail::to(auth()->user()->email)->send(new OrderConfirmationMail($order));
        }
    } catch (\Exception $e) {
        \Log::error("Email confirmation failed: " . $e->getMessage());
    }

    // Automatically trigger WhatsApp Notification
    WhatsAppNotification::sendOrderConfirmation($order);

    return redirect()->route('order.success', $order->id);
})->middleware('auth')->name('checkout.place');

Route::get('/order/success/{order}', function (Order $order) {
    return view('store.order-success', compact('order'));
})->middleware('auth')->name('order.success');

Route::get('/admin/orders/print-bulk', function (\Illuminate\Http\Request $request) {
    $ids = explode(',', $request->input('ids', ''));
    $orders = \App\Models\Order::with('items.product')->whereIn('id', $ids)->get();
    
    if ($orders->isEmpty()) {
        return redirect()->back();
    }

    return view('admin.print-orders', compact('orders'));
})->middleware(['auth', 'web'])->name('admin.orders.print');

Route::get('/track-order', function (Request $request) {
    $order = null;
    if ($request->has('query')) {
        $query = $request->input('query');
        $order = Order::where('id', $query)
            ->orWhere('tracking_number', $query)
            ->orWhere('phone', $query)
            ->first();
    }
    return view('frontend.track-order', compact('order'));
})->name('track.order');

Route::get('/my-orders', function () {
    if (!Auth::check()) {
        return redirect()->route('filament.admin.auth.login');
    }

    $orders = Order::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

    $selectedOrder = null;
    if (request()->has('order_id')) {
        $selectedOrder = $orders->firstWhere('id', request('order_id'));
    }

    return view('frontend.my-orders', compact('orders', 'selectedOrder'));
})->middleware(['auth', 'web'])->name('customer.orders');

// 6. Load Breeze Authentication Routes (Login, Register, Logout)
require __DIR__.'/auth.php';