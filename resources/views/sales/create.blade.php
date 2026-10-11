@extends('layouts.app', ['title' => 'Punto de Venta POS', 'header' => 'Punto de Venta (POS)'])

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col lg:flex-row gap-6">

    <!-- Left Side: Product Selector -->
    <div class="lg:w-7/12 flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Catálogo de Productos para Venta</h2>
                <p class="text-xs text-slate-500">Haga clic sobre un producto para añadirlo al carrito</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ $products->count() }} disponibles
            </span>
        </div>

        <!-- Product Search & Category Filter -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            <input type="text" id="posSearch" placeholder="🔍 Buscar por nombre o SKU..." oninput="filterPosProducts()"
                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            <select id="posCategoryFilter" onchange="filterPosProducts()"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                <option value="">Todas las Categorías</option>
                @foreach($products->pluck('category.name')->unique()->filter() as $catName)
                <option value="{{ $catName }}">{{ $catName }}</option>
                @endforeach
            </select>
        </div>

        <!-- Scrollable Products Grid -->
        <div id="productsGrid" class="flex-1 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 pr-1">
            @forelse($products as $product)
            <div class="pos-item p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:shadow-md transition cursor-pointer flex flex-col justify-between bg-slate-50/50 hover:bg-white group"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-sku="{{ $product->sku }}"
                 data-price="{{ $product->sale_price }}"
                 data-stock="{{ $product->stock }}"
                 data-category="{{ $product->category->name ?? '' }}"
                 onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku }}', {{ $product->sale_price }}, {{ $product->stock }})">

                <div>
                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-500 mb-1">
                        <span>{{ $product->sku }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $product->stock <= 5 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                            Stock: {{ $product->stock }}
                        </span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-brand-600 line-clamp-2 leading-snug">
                        {{ $product->name }}
                    </h4>
                </div>

                <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                    <span class="text-sm font-black text-slate-900 font-mono">
                        ${{ number_format($product->sale_price, 2) }}
                    </span>
                    <button type="button" class="px-2 py-1 rounded-lg bg-brand-50 group-hover:bg-brand-600 text-brand-600 group-hover:text-white text-xs font-bold transition">
                        + Añadir
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 text-xs">
                No hay productos activos con stock disponible para la venta.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Side: Interactive Cart & Checkout Form -->
    <div class="lg:w-5/12 flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Carrito de Compra
            </h2>
            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:underline font-semibold">
                Vaciar
            </button>
        </div>

        <!-- Actual POST Form submission to SaleController@store -->
        <form id="posCheckoutForm" action="{{ route('sales.store') }}" method="POST" class="flex-1 flex flex-col justify-between overflow-hidden mt-3">
            @csrf

            <!-- Client Selection & Payment Mode -->
            <div class="space-y-3 pb-3 border-b border-slate-100">
                <div>
                    <label for="customer_id" class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cliente Receptor</label>
                    <select id="customer_id" name="customer_id" onchange="toggleCustomerNameInput(this)"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">Cliente Ocasional (Mostrador)</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->tax_id ?? 'Sin documento' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="occasionalNameContainer" class="block">
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" placeholder="Nombre opcional del comprador en mostrador..."
                           class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label for="payment_method" class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Método de Pago</label>
                        <select id="payment_method" name="payment_method" required
                                class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none font-semibold">
                            <option value="efectivo" {{ old('payment_method') === 'efectivo' ? 'selected' : '' }}>💵 Efectivo</option>
                            <option value="tarjeta" {{ old('payment_method') === 'tarjeta' ? 'selected' : '' }}>💳 Tarjeta Débito/Crédito</option>
                            <option value="transferencia" {{ old('payment_method') === 'transferencia' ? 'selected' : '' }}>🏦 Transferencia Bancaria</option>
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nota Interna</label>
                        <input type="text" id="notes" name="notes" placeholder="Ej. Orden #102..."
                               class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Dynamic Cart Items Container -->
            <div id="cartItemsList" class="flex-1 overflow-y-auto py-3 space-y-2.5 my-2">
                <!-- Injected via JavaScript -->
                <div id="emptyCartMessage" class="text-center py-10 text-slate-400 text-xs">
                    El carrito está vacío.<br>Haga clic en un producto para agregarlo.
                </div>
            </div>

            <!-- Hidden container for form inputs items[i][product_id], items[i][quantity] -->
            <div id="hiddenInputsContainer"></div>

            <!-- Summary & Confirm Button -->
            <div class="pt-3 border-t border-slate-200 space-y-2 bg-slate-50 -mx-5 -mb-5 p-5 rounded-b-2xl">
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Subtotal:</span>
                    <span id="cartSubtotal" class="font-mono font-semibold">$0.00</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Impuestos (0%):</span>
                    <span class="font-mono font-semibold">$0.00</span>
                </div>
                <div class="flex items-center justify-between text-base font-extrabold text-slate-900 pt-1 border-t border-slate-200">
                    <span>TOTAL A PAGAR:</span>
                    <span id="cartTotal" class="font-mono text-emerald-600 text-lg">$0.00</span>
                </div>

                <button type="submit" id="confirmSaleBtn" disabled
                        class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm shadow-lg shadow-emerald-600/20 transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirmar y Registrar Venta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- POS Cart Script -->
<script>
    let cart = [];

    function addToCart(id, name, sku, price, maxStock) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.quantity < maxStock) {
                existing.quantity++;
            } else {
                alert(`No es posible agregar más unidades. El stock máximo disponible para '${name}' es de ${maxStock} unidades.`);
            }
        } else {
            cart.push({
                id: id,
                name: name,
                sku: sku,
                price: price,
                quantity: 1,
                maxStock: maxStock
            });
        }
        renderCart();
    }

    function updateQuantity(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const newQty = item.quantity + delta;
        if (newQty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (newQty > item.maxStock) {
            alert(`Stock máximo alcanzado (${item.maxStock} unidades).`);
        } else {
            item.quantity = newQty;
        }
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function clearCart() {
        if (cart.length > 0 && confirm('¿Desea vaciar todos los artículos del carrito?')) {
            cart = [];
            renderCart();
        }
    }

    function renderCart() {
        const container = document.getElementById('cartItemsList');
        const emptyMsg = document.getElementById('emptyCartMessage');
        const hiddenInputs = document.getElementById('hiddenInputsContainer');
        const confirmBtn = document.getElementById('confirmSaleBtn');

        container.innerHTML = '';
        hiddenInputs.innerHTML = '';

        if (cart.length === 0) {
            container.appendChild(emptyMsg);
            document.getElementById('cartSubtotal').textContent = '$0.00';
            document.getElementById('cartTotal').textContent = '$0.00';
            confirmBtn.disabled = true;
            return;
        }

        confirmBtn.disabled = false;
        let subtotal = 0;

        cart.forEach((item, index) => {
            const lineTotal = item.price * item.quantity;
            subtotal += lineTotal;

            // Render Item Card
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs';
            div.innerHTML = `
                <div class="overflow-hidden pr-2">
                    <span class="font-bold text-slate-800 block truncate">${item.name}</span>
                    <span class="text-[11px] font-mono text-slate-500">${item.sku} • $${item.price.toFixed(2)} c/u</span>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <div class="flex items-center border border-slate-300 rounded-lg bg-white">
                        <button type="button" onclick="updateQuantity(${item.id}, -1)" class="px-2 py-0.5 text-slate-600 hover:bg-slate-100 rounded-l font-bold">-</button>
                        <span class="px-2 font-mono font-bold text-slate-800">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${item.id}, 1)" class="px-2 py-0.5 text-slate-600 hover:bg-slate-100 rounded-r font-bold">+</button>
                    </div>
                    <span class="font-mono font-bold text-slate-900 w-16 text-right">$${lineTotal.toFixed(2)}</span>
                    <button type="button" onclick="removeFromCart(${item.id})" class="text-rose-500 hover:text-rose-700 p-1 font-bold" title="Eliminar">✕</button>
                </div>
            `;
            container.appendChild(div);

            // Hidden inputs for form submit
            hiddenInputs.innerHTML += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            `;
        });

        document.getElementById('cartSubtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('cartTotal').textContent = `$${subtotal.toFixed(2)}`;
    }

    function toggleCustomerNameInput(select) {
        const container = document.getElementById('occasionalNameContainer');
        if (select.value === '') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    function filterPosProducts() {
        const query = document.getElementById('posSearch').value.toLowerCase();
        const cat = document.getElementById('posCategoryFilter').value;
        const items = document.querySelectorAll('.pos-item');

        items.forEach(el => {
            const name = el.getAttribute('data-name').toLowerCase();
            const sku = el.getAttribute('data-sku').toLowerCase();
            const itemCat = el.getAttribute('data-category');

            const matchQuery = name.includes(query) || sku.includes(query);
            const matchCat = cat === '' || itemCat === cat;

            if (matchQuery && matchCat) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
    }
</script>
@endsection
