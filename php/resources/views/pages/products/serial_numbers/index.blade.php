@extends('layouts.app')
@php
$thead = [
    'serial_number' =>  'Serial Number',
    'sku' => 'SKU',
    'status' => 'Status',
    'note' => 'Note'
];
@endphp

@section('content')
    <article class="flex flex-col flex-1 overflow-hidden min-h-0">
        <x-filter-form 
            route="{{route('products.serial_number.index', $product->id)}}"
        >
            <div class="flex flex-col lg:flex-row gap-3 w-full">
                <x-input
                    id="search_name"
                    name="name"
                    type="text"
                    label="Name"
                    placeholder=" "
                    :showPlaceHolder="true"
                    value="{{request()->get('name') && request()->get('name') !== 'null' ? request()->get('name') : ''}}"
                />
                <x-select
                    id="search_is_active"
                    name="is_active"
                    label="Is Active"
                    :showPlaceHolder="true"
                >
                    <option>All</option>
                    <option value="true" {{ request()->get('is_active') === 'true' ? 'selected' : '' }}>Active</option>
                    <option value="false" {{ request()->get('is_active') === 'false' ? 'selected' : '' }}>In active</option>
                </x-select>
            </div>
            <div class="flex gap-3 w-full flex-1">
                <x-button variant="info" type="submit" class="rounded-md flex gap-2 items-center justify-center flex-1 lg:flex-initial">
                    <x-search-icon class="fill-white" />
                    <span>Search</span>
                </x-button>
                <x-button variant="default" href="{{ route('products.index') }}" class="rounded-md flex gap-2 items-center flex-1 lg:flex-initial">
                    <span>Clear</span>
                </x-button>
            </div>
        </x-card>
        <div class="flex-1 grid grid-cols-12 gap-2 min-h-0 relative">
            <x-table
                :thead="$thead"
                :tbody="$serialNumbers"
                :title="'Serial Numbers for '.$product->name.''"
                cardHeaderClass="flex flex-row py-3 px-4"
                titleClass="text-lg font-semibold text-gray-800"
                customNoDataMessage="No products found. Please adjust your filters or change page."
                cardContainerClass="col-span-9"
                tableContainerClass="flex-1 lg:overflow-y-none {{$serialNumbers && count($serialNumbers) < 5 ? 'h-full' : ''}}"
            >
                <x-slot:dataActions class="relative w-20 mx-auto" dataActionsClassHeader="flex items-center justify-end w-20">
                    <x-action-menu />
                </x-slot:dataActions>
            </x-table>
            <x-card class="col-span-3 p-0 max-h-fit">
                <x-card-header class="flex flex-row py-3 px-4" id="cardInfoTitle">Add Item Info</x-card-header>
                <form method="POST" class="mt-2 p-2" action="{{route('products.serial_number.store', $product->id)}}" id="formSerialNumber">
                    @csrf
                    <x-input class="mb-2" id="serial_number" name="serial_number" type="text" placeholder="Serial Number" label="Serial Number" required />
                    <x-input class="mb-2" id="sku" name="sku" type="text" placeholder="SKU" label="SKU"/>
                    <x-select
                        name="status"
                        label="Status"
                        :showPlaceHolder="true"
                        class="mb-2"
                    >
                        @foreach(config('const.serial_numbers_status') as $status)
                            <option value="{{ $status }}" {{ request()->get('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </x-select>
                    <x-textarea
                        id="note"
                        name="note"
                        type="text"
                        placeholder="Note"
                        label="Note"
                        rows="4"
                        cols="50"
                        class="mb-2"
                    />
                    <div class="flex flex-row gap-2">
                        <x-button variant="success" type="submit">Save</x-button>
                        <x-button variant="default" type="button" id="cancelBtn" class="hidden">Cancel</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </article>
@endsection
@section('footer')
    <x-modal header="Add Product" headerClass="modalTitle">
        <div id="modalContent"></div>
    </x-modal>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formTitle = document.getElementById('cardInfoTitle');
            const form = document.getElementById('formSerialNumber');
            const modalElement = document.querySelector('#modal');
            const modalContent = document.querySelector('#modalContent');
            const modalTitle = modalElement.querySelector('.modalTitle');
            
            document.addEventListener('click', function (e) {
                const editBtn = e.target.closest('.editActionButton');
                const deleteBtn = e.target.closest('.deleteActionButton');
                const cancelBtn = document.getElementById('cancelBtn');

                if (editBtn) {
                    const rowData = JSON.parse(editBtn.closest('td').getAttribute('data-pass'));
                    formTitle.innerText = 'Edit Info';

                    form.querySelector('[name="serial_number"]').value = rowData.serial_number;
                    form.querySelector('[name="sku"]').value = rowData.sku;
                    form.querySelector('[name="status"]').value = rowData.status;
                    form.querySelector('[name="note"]').value = rowData.note;

                    // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                    const baseUrl = "{{ route('products.serial_number.update', [$product->id, ':id']) }}"; // Blade generates base URL
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
                    cancelBtn.classList.remove('hidden');
                }

                if (deleteBtn) {
                    const rowData = JSON.parse(deleteBtn.closest('td').getAttribute('data-pass'));
                    
                    // 1. Change Modal Header
                    modalTitle.innerText = 'Delete product info ' + rowData.serial_number + '?';
                    modalContent.innerHTML = `
                        <x-delete-serial-number-form id="serialNumberForm" method="POST"/>
                    `;
                    const form = document.querySelector('#serialNumberForm');

                    // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                    const baseUrl = "{{ route('products.serial_number.destroy', [$product->id, ':id']) }}"; // Blade generates base URL
                    const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                    const urlTemplate = params ? `${baseUrl}?${params}` : baseUrl;
                    form.action = urlTemplate.replace(':id', rowData.id);

                    modalElement.classList.remove('hidden');
                    modalElement.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }
            })
            document.getElementById('cancelBtn').addEventListener('click', function (e) {
                formTitle.innerText = 'Add Info';

                form.querySelector('[name="serial_number"]').value = null;
                form.querySelector('[name="sku"]').value = null;
                form.querySelector('[name="status"]').value = 'IN';
                form.querySelector('[name="note"]').value = null;
                if(form.querySelector('input[name="_method"]')) {
                    form.querySelector('[name="_method"]').remove();
                }
                // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                const baseUrl = "{{ route('products.serial_number.store', $product->id) }}"; // Blade generates base URL
                const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                const urlTemplate = params ? `${baseUrl}?${params}` : baseUrl;
                form.action = urlTemplate;
                e.target.classList.add('hidden');
            })
        })
    </script>
@endpush