@extends('layouts.app', ['title' => 'Punto de Venta POS', 'header' => 'Punto de Venta (POS)'])

@section('content')
<div class="min-h-[calc(100vh-140px)] lg:h-[calc(100vh-140px)] flex flex-col lg:flex-row gap-6 pb-6 lg:pb-0">

    <!-- Left Side: Product Selector -->
    <div class="w-full lg:w-7/12 flex flex-col bg-white rounded-lg border border-slate-200 shadow-sm p-5 min-h-[420px] lg:min-h-0 lg:overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Catálogo de Productos para Venta</h2>
                <p class="text-xs text-slate-500">Haga clic o use Enter sobre un producto para añadirlo al carrito</p>
            </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ $products->count() }} disponibles
            </span>
        </div>

        <!-- Product Search & Category Filter -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            <div class="relative">
                <label for="posSearch" class="sr-only">Buscar por nombre o SKU</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" id="posSearch" placeholder="Buscar por nombre o SKU (F2)..." oninput="filterPosProducts()"
                           class="w-full pl-8 pr-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                           aria-label="Buscar producto por nombre o código SKU">
                </div>
            </div>
            <div>
                <label for="posCategoryFilter" class="sr-only">Filtrar por categoría</label>
                <select id="posCategoryFilter" onchange="filterPosProducts()"
                        class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                        aria-label="Filtrar catálogo por categoría">
                    <option value="">Todas las Categorías</option>
                    @foreach($products->pluck('category.name')->unique()->filter() as $catName)
                    <option value="{{ $catName }}">{{ $catName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Scrollable Products Grid -->
        <div id="productsGrid" class="flex-1 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 pr-1">
            @forelse($products as $product)
            <button type="button"
                 class="pos-item text-left p-3 rounded-md border border-slate-200 hover:border-brand-800 focus:border-brand-800 focus:ring-1 focus:ring-brand-800 focus:outline-none hover:shadow-xs transition cursor-pointer flex flex-col justify-between bg-white hover:bg-slate-50/70 focus:bg-slate-50/70 group w-full"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-sku="{{ $product->sku }}"
                 data-price="{{ $product->sale_price }}"
                 data-stock="{{ $product->stock }}"
                 data-category="{{ $product->category->name ?? '' }}"
                 onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku }}', {{ $product->sale_price }}, {{ $product->stock }})"
                 aria-label="Añadir {{ $product->name }} al carrito. Precio: ${{ number_format($product->sale_price, 2) }}. Stock disponible: {{ $product->stock }}">

                <div class="w-full">
                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-500 mb-1">
                        <span class="font-medium text-slate-600">{{ $product->sku }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium {{ $product->stock <= 5 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700' }}">
                            Stock: {{ $product->stock }}
                        </span>
                    </div>
                    <h4 class="text-xs font-semibold text-slate-900 group-hover:text-brand-900 group-focus:text-brand-900 line-clamp-2 leading-snug">
                        {{ $product->name }}
                    </h4>
                </div>

                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between w-full">
                    <span class="text-sm font-bold text-slate-900 font-mono tabular-nums">
                        ${{ number_format($product->sale_price, 2) }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 group-hover:bg-brand-900 group-focus:bg-brand-900 text-slate-700 group-hover:text-white group-focus:text-white transition">
                        + Añadir
                    </span>
                </div>
            </button>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 text-xs">
                No hay productos activos con stock disponible para la venta.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Side: Interactive Cart & Checkout Form -->
    <div class="w-full lg:w-5/12 flex flex-col bg-white rounded-lg border border-slate-200 shadow-sm p-5 lg:overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Carrito de Venta
            </h2>
            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-medium transition">
                Vaciar
            </button>
        </div>

        <!-- Actual POST Form submission to SaleController@store -->
        <form id="posCheckoutForm" action="{{ route('sales.store') }}" method="POST" class="flex-1 flex flex-col justify-between overflow-hidden mt-3">
            @csrf

            <!-- Client Selection & Payment Mode -->
            <div class="space-y-3 pb-3 border-b border-slate-200">
                <div>
                    <label for="customer_id" class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Cliente Receptor</label>
                    <select id="customer_id" name="customer_id" onchange="toggleCustomerNameInput(this)"
                            class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
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
                           class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label for="payment_method" class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Método de Pago</label>
                        <select id="payment_method" name="payment_method" required
                                class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none font-medium">
                            <option value="efectivo" {{ old('payment_method') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="tarjeta" {{ old('payment_method') === 'tarjeta' ? 'selected' : '' }}>Tarjeta Débito / Crédito</option>
                            <option value="transferencia" {{ old('payment_method') === 'transferencia' ? 'selected' : '' }}>Transferencia Bancaria</option>
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Nota Interna</label>
                        <input type="text" id="notes" name="notes" placeholder="Ej. Orden #102..."
                               class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
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
            <div class="pt-3 border-t border-slate-200 space-y-2 bg-slate-50 -mx-5 -mb-5 p-5 rounded-b-lg">
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Subtotal:</span>
                    <span id="cartSubtotal" class="font-mono font-medium">$0.00</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Impuestos (0%):</span>
                    <span class="font-mono font-medium">$0.00</span>
                </div>
                <div class="flex items-center justify-between text-sm font-bold text-slate-900 pt-1.5 border-t border-slate-200">
                    <span>TOTAL A PAGAR:</span>
                    <span id="cartTotal" class="font-mono text-brand-950 text-base font-bold">$0.00</span>
                </div>

                <button type="submit" id="confirmSaleBtn" disabled
                        class="w-full py-2.5 rounded-md bg-brand-900 hover:bg-brand-950 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-medium text-xs tracking-wide shadow-sm transition flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
            div.className = 'flex items-center justify-between p-2.5 rounded-md bg-slate-50 border border-slate-200 text-xs';
            div.innerHTML = `
                <div class="overflow-hidden pr-2">
                    <span class="font-medium text-slate-900 block truncate">${item.name}</span>
                    <span class="text-[11px] font-mono tabular-nums text-slate-500">${item.sku} • $${item.price.toFixed(2)} c/u</span>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <div class="flex items-center border border-slate-300 rounded-md bg-white overflow-hidden shadow-xs">
                        <button type="button" onclick="updateQuantity(${item.id}, -1)" class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-slate-100 font-bold text-sm touch-manipulation focus:outline-none focus:ring-1 focus:ring-brand-800" aria-label="Disminuir una unidad de ${item.name}">-</button>
                        <span class="w-7 text-center font-mono font-medium text-slate-900 tabular-nums text-xs">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${item.id}, 1)" class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-slate-100 font-bold text-sm touch-manipulation focus:outline-none focus:ring-1 focus:ring-brand-800" aria-label="Aumentar una unidad de ${item.name}">+</button>
                    </div>
                    <span class="font-mono font-bold text-slate-900 w-16 text-right tabular-nums text-xs">$${lineTotal.toFixed(2)}</span>
                    <button type="button" onclick="removeFromCart(${item.id})" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition focus:outline-none focus:ring-1 focus:ring-rose-500" title="Eliminar artículo" aria-label="Eliminar ${item.name} del carrito">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
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

    // POS Keyboard Accelerators & Barcode Scanner Integration
    document.addEventListener('keydown', function(e) {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const isInputField = (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select');

        // F2: Focus Search from anywhere
        if (e.key === 'F2') {
            e.preventDefault();
            const searchInput = document.getElementById('posSearch');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            return;
        }

        // '/': Quick search trigger only when not typing inside another input
        if (e.key === '/' && !isInputField) {
            e.preventDefault();
            const searchInput = document.getElementById('posSearch');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            return;
        }

        // F4 or Ctrl+Enter: Confirm and trigger checkout if cart has items
        if (e.key === 'F4' || (e.ctrlKey && e.key === 'Enter')) {
            const confirmBtn = document.getElementById('confirmSaleBtn');
            if (confirmBtn && !confirmBtn.disabled) {
                e.preventDefault();
                confirmBtn.click();
            }
            return;
        }

        // Escape: Clear search or blur active field
        if (e.key === 'Escape') {
            const searchInput = document.getElementById('posSearch');
            if (document.activeElement === searchInput) {
                if (searchInput.value !== '') {
                    searchInput.value = '';
                    filterPosProducts();
                } else {
                    searchInput.blur();
                }
            }
        }
    });

    // Barcode scanner & direct Enter support on search field
    const searchInput = document.getElementById('posSearch');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const visibleItems = Array.from(document.querySelectorAll('.pos-item')).filter(el => el.style.display !== 'none');
                if (visibleItems.length === 1) {
                    visibleItems[0].click();
                    searchInput.value = '';
                    filterPosProducts();
                    searchInput.focus();
                } else if (visibleItems.length > 1) {
                    // If exact SKU match exists among visible items, select it
                    const currentVal = searchInput.value.trim().toLowerCase();
                    const exactSku = visibleItems.find(el => el.getAttribute('data-sku').toLowerCase() === currentVal);
                    if (exactSku) {
                        exactSku.click();
                        searchInput.value = '';
                        filterPosProducts();
                        searchInput.focus();
                    }
                }
            }
        });
    }
</script>
@endsection
