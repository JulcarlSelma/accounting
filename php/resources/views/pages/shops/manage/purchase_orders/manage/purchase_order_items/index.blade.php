@extends('pages.shops.manage.app')
@php
$thead = [
    'product.name' =>  'Name',
    'quantity' =>  'Quantity',
    'price' =>  [
        'header' => 'Price',
        'prefix' => config('const.money').' ',
        'format' => 'money',
    ],
    'total' => [
        'header' => 'Total',
        'prefix' => config('const.money').' ',
        'format' => 'money'
    ],
];
@endphp

@section('shop_content')
    <article class="flex flex-col flex-1 min-h-0">
        <x-filter-form 
            route="{{route('shops.purchase-orders.index', $shop->id)}}"
            class="shrink-0"
        >
            <div class="flex flex-col lg:flex-row gap-3 w-full">
                <x-select
                    id="search_status"
                    name="status"
                    label="Status"
                    :showPlaceHolder="true"
                >
                    <option>All</option>
                    @foreach(config('const.purchase_order_status') as $status)
                        <option value="{{$status}}" {{ request()->get('status') === $status ? 'selected' : '' }}>{{$status}}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="flex gap-3 w-full flex-1">
                <x-button variant="info" type="submit" class="rounded-md flex gap-2 items-center justify-center flex-1 lg:flex-initial">
                    <x-search-icon class="fill-white" />
                    <span>Search</span>
                </x-button>
                <x-button variant="default" href="{{route('shops.purchase-orders.index', $shop->id)}}" class="rounded-md flex gap-2 items-center flex-1 lg:flex-initial">
                    <span>Clear</span>
                </x-button>
            </div>
        </x-filter-form>
        <x-table
            :thead="$thead"
            :tbody="$purchase_order->orders"
            title=" "
            cardContainerID="tableContainer"
            cardContainerClass="flex-1 min-w-0 transition-all duration-500"
            cardHeaderClass="flex flex-col lg:flex-row py-3 px-4"
            titleClass="text-lg font-semibold text-gray-800"
            :booleanMessage="[0 => 'In Active', 1 => 'Active']"
            customNoDataMessage="No purchased order items found. Please adjust your filters or change page."
            tableContainerClass="flex-1 lg:overflow-y-none {{count($purchase_order->orders) < 5 ? 'h-full' : ''}}"
        >
            <x-slot:leftPocket>
                <div class="flex flex-col leading-none w-full">
                    <p class="tex-xs">PO #: {{$purchase_order->po_number}}</p>
                    <p class="tex-xs">Order Date: {{$purchase_order->order_date}}</p>
                    <p class="tex-xs">Expected Date: {{$purchase_order->expected_date}}</p>
                    <p class="tex-xs">No. of Items: {{count($purchase_order->orders)}}</p>
                    <p class="tex-xs">Subtotal: {{config('const.money').' '.number_format($purchase_order->subtotal, 2, '.', ',')}}</p>
                </div>
            </x-slot:leftPocket>
            <x-slot:rightPocket>
                <div class="flex flex-row gap-2 w-full lg:w-auto h-[42px]">
                    <x-button id="addPurchaseOrderItem" variant="success" class="rounded-md text-md w-full lg:w-auto">Add</x-button>
                    <x-button href="{{route('shops.purchase-orders.index', $shop->id)}}" variant="default" class="rounded-md text-md w-full lg:w-auto whitespace-nowrap">Go Back</x-button>
                </div>
            </x-slot:rightPocket>
            <x-slot:dataActions class="relative w-20 mx-auto" dataActionsClassHeader="flex items-center justify-end w-20">
                <x-action-menu/>
            </x-slot:dataActions>
        </x-table>
    </article>
@endsection

@section('footer')
    <x-modal header="Add Items" headerClass="modalTitle" class="min-h-0 flex-1">
        <div id="modalContent" class="h-full"></div>
    </x-modal>
@endsection
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.querySelector('#modal');
            const modalContent = document.querySelector('#modalContent');
            const modalTitle = modalElement.querySelector('.modalTitle');
            const purchaseOrder = @json($purchase_order);
            const activeProducts = @json($dropdowns['suppliers'][0]->pricings);
            
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.actionButton');
                const addBtn = e.target.closest('#addPurchaseOrderItem');
                const editBtn = e.target.closest('.editActionButton');
                const deleteBtn = e.target.closest('.deleteActionButton');
                const multiSelectContainer = e.target.closest('.multi-select-container');

                // -- ADD Logic
                if (addBtn) {
                    modalTitle.innerText = 'Add products for PO #: ' + purchaseOrder.po_number;
                    modalContent.innerHTML = `
                        <x-purchase-order-modify-form id="purchaseOrderItemForm"  method="POST" autocomplete="off"/>
                    `;

                    modalElement.classList.remove('hidden');
                    modalElement.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                    const optionsContainer = document.querySelector('.multi-select-options');

                    let html = '';
                    activeProducts.forEach(pricing => {
                        html += `<x-input
                            type="checkbox"
                            label="${pricing.product.name}"
                            value="${pricing.product.id}" 
                            id="multi-select-${pricing.product.id}" 
                            data-id="${pricing.product.id}"
                            class="rounded border-gray-300 whitespace-nowrap"
                        />`;
                    });
                    optionsContainer.innerHTML = html;

                    const form = document.querySelector('#purchaseOrderItemForm');
                    
                    if(form.querySelector('input[name="_method"]')) {
                        form.querySelector('[name="_method"]').remove();
                    }
                    const baseUrl = "{{ route('products.units.store') }}"; // Blade generates base URL
                    const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                    form.action = params ? `${baseUrl}?${params}` : baseUrl;
                }
                    
                // --- EDIT Logic ---
                if (editBtn) {
                    const rowData = JSON.parse(editBtn.closest('td').getAttribute('data-pass'));
                    
                    // 1. Change Modal Header
                    modalTitle.innerText = 'Edit Item Quantity: ' + rowData.product.name;
                    modalContent.innerHTML = `
                        <x-edit-purchase-order-item-form id="purchaseOrderItemForm" method="POST" autocomplete="off"/>
                    `;
                    const form = document.querySelector('#purchaseOrderItemForm');
                    
                    // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                    form.querySelector('[name="quantity"]').value = rowData.quantity;
                    form.querySelector('[name="total"]').value = rowData.total;
                    
                    const baseUrl = "{{ route('shops.staffs.update', [$shop->id, ':id']) }}"; // Blade generates base URL
                    const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                    const urlTemplate = params ? `${baseUrl}?${params}` : baseUrl;
                    form.action = urlTemplate.replace(':id', rowData.id);
                    
                    // 3. Inject Method Spoofing for PUT
                    if(!form.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }

                    modalElement.classList.remove('hidden');
                    modalElement.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                
                // --- DELETE Logic ---
                if (deleteBtn) {
                    const rowData = JSON.parse(deleteBtn.closest('td').getAttribute('data-pass'));
                    
                    // 1. Change Modal Header
                    modalTitle.innerText = 'Delete Item from this PO?';
                    modalContent.innerHTML = `
                        <x-delete-purchase-order-item-form id="purchaseOrderItemForm" method="POST"/>
                    `;
                    const form = document.querySelector('#purchaseOrderItemForm');

                    // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                    const baseUrl = "{{ route('shops.staffs.destroy', [$shop->id, ':id']) }}"; // Blade generates base URL
                    const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                    const urlTemplate = params ? `${baseUrl}?${params}` : baseUrl;
                    form.action = urlTemplate.replace(':id', rowData.id);

                    modalElement.classList.remove('hidden');
                    modalElement.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                
                if (multiSelectContainer) {
                    const productQuantityContainer = document.getElementById('multiple-product-inputs');
                    const subtotalInput = document.getElementById('subtotal');
                    const totalInput = document.getElementById('total');
                    const checkboxes = multiSelectContainer.querySelectorAll('input[type="checkbox"]');
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => ({
                            label: cb.nextElementSibling?.innerText || cb.value,
                            id: cb.dataset.id
                        }));
                    let html = '';
                    if (selected.length > 0) {
                        let inputs = '';
                        let subtotal = 0;
                        let total = 0;
                        for (var x = 0; x < selected.length; x++) {
                            let id = selected[x].id;
                            let label = selected[x].label;
                            let findProduct = activeProducts.find((e) => {
                                return e.product.id == id
                            })
                            let price = parseFloat(findProduct.price) || 0
                            let qty = 1;

                            inputs += `<div>
                                <x-input
                                    type="hidden"
                                    name="product_ids[${id}][product_id]"
                                    value="${id}"
                                    class="hidden"
                                />
                                <x-input
                                    type="hidden"
                                    name="product_ids[${id}][price]"
                                    value="${price}"
                                    class="hidden"
                                />
                                <div class="flex flex-row gap-2 items-center justify-between">
                                    <div class="w-full overflow-hidden truncate flex-1">
                                        <x-label class="whitespace-nowrap">${label}</x-label>
                                    </div>
                                    <x-input
                                        label="Price"
                                        value="${findProduct.price}"
                                        inputContainerClass="w-full max-w-20 lg:w-50 shrink-0"
                                        disabled
                                    />
                                    <x-input
                                        type="number"
                                        name="product_ids[${id}][quantity]"
                                        class="quantity-input"
                                        label="Qty"
                                        value="1"
                                        min="1"
                                        inputContainerClass="w-full max-w-20 lg:w-50 shrink-0"
                                        data-product-id="${id}"
                                        data-price="${price}"
                                    />
                                </div>
                            </div>`;

                            let currentPrice = price * qty;
                            subtotal = subtotal + currentPrice;
                        }
                        total = parseFloat(subtotal);
                        subtotalInput.value = subtotal.toFixed(2);
                        totalInput.value = total.toFixed(2);
                        productQuantityContainer.innerHTML = inputs;
                    } else {
                        productQuantityContainer.innerHTML = '';
                    }
                }
            });

            function recalculateTotals() {
                let subtotal = 0;

                const productQuantityContainer = document.getElementById('multiple-product-inputs');
                const rows = productQuantityContainer.querySelectorAll('.quantity-input');
                const subtotalInput = document.getElementById('subtotal');
                const totalInput = document.getElementById('total');

                rows.forEach((input) => {
                    let dataset = input.dataset;
                    let id = dataset.productId;
                    let qty = parseFloat(input.value || 0)
                    let price = dataset.price;

                    subtotal += price * qty;
                });

                subtotalInput.value = subtotal.toFixed(2);
                totalInput.value = subtotal.toFixed(2);
            }

            document.addEventListener('change', function(e) {
                if (e.target && e.target.matches('.quantity-input')) {
                    recalculateTotals();
                }
            });
        });
    </script>
@endpush