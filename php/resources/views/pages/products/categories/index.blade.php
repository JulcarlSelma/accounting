@extends('layouts.app')
@php
$thead = [
    'name' => [
        'header' => 'Name',
        'tdClass' => 'w-[30vw] max-w-[30vw] lg:w-[20vw] lg:max-w-[20vw]',
    ],
    'description' => [
        'header' => 'Description',
        'tdClass' => 'w-[50vw] max-w-[50vw] lg:w-[30vw] lg:max-w-[30vw] overflow-hidden text-ellipsis',
    ],
    'is_active' => [
        'header' => 'Active',
        'cast' => 'span',
        'tdContentClass' => 'px-2 py-1 rounded-full text-xs font-semibold',
        'tdContentClassActive' => 'bg-green-100 text-green-800',
        'tdContentClassInactive' => 'bg-red-100 text-red-800',
    ],
];
@endphp

@section('content')
    <article class="flex flex-col flex-1 min-h-0">
        <x-filter-form
            route="{{route('products.categories.index')}}"
        >
            <div class="flex gap-3 w-full">
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
                <x-button variant="default" href="{{ route('products.categories.index') }}" class="rounded-md flex gap-2 items-center flex-1 lg:flex-initial">
                    <span>Clear</span>
                </x-button>
            </div>
        </x-card>
        <x-table
            :thead="$thead"
            :tbody="$categories"
            title="Categories"
            cardHeaderClass="flex flex-row py-3 px-4"
            titleClass="text-lg font-semibold text-gray-800"
            :booleanMessage="[0 => 'In Active', 1 => 'Active']"
            customNoDataMessage="No categories found. Please adjust your filters or change page."
            tableContainerClass="flex-1 lg:overflow-y-none h-full"
        >
            <x-slot:rightPocket>
                <x-button id="addCategory" variant="success" data-modal-open class="rounded-md text-md">Add</x-button>
            </x-slot:rightPocket>
            <x-slot:dataActions class="flex items-center justify-center relative w-20" dataActionsClassHeader="flex items-center justify-end w-20">
                <x-action-menu />
            </x-slot:dataActions>
        </x-table>
    </article>
@endsection
@section('footer')
    <x-modal header="Add Category" headerClass="modalTitle">
        <div id="modalContent"></div>
    </x-modal>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.querySelector('#modal');
        const modalContent = document.querySelector('#modalContent');
        const modalTitle = modalElement.querySelector('.modalTitle');

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.actionButton');
            const addBtn = e.target.closest('#addCategory');
            const editBtn = e.target.closest('.editActionButton');
            const deleteBtn = e.target.closest('.deleteActionButton');

            // -- ADD Logic
            if (addBtn) {
                modalTitle.innerText = 'Add Category';
                modalContent.innerHTML = `
                   <x-category-form id="categoryForm" method="POST" autocomplete="off"/>
                `;

                const form = document.querySelector('#categoryForm');
                form.querySelector('[name="name"]').value = null;
                form.querySelector('[name="description"]').value = null;
                form.querySelector('[name="is_active"]').value = null;
                
                if(form.querySelector('input[name="_method"]')) {
                    form.querySelector('[name="_method"]').remove();
                }
                const baseUrl = "{{ route('products.categories.store') }}"; // Blade generates base URL
                const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                form.action = params ? `${baseUrl}?${params}` : baseUrl; 
            }

            // --- EDIT Logic ---
            if (editBtn) {
                const rowData = JSON.parse(editBtn.closest('td').getAttribute('data-pass'));
                
                // 1. Change Modal Header
                modalTitle.innerText = 'Edit Category: ' + rowData.name;
                modalContent.innerHTML = `
                   <x-category-form id="categoryForm" method="POST" autocomplete="off"/>
                `;
                const form = document.querySelector('#categoryForm');
                
                // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                const baseUrl = "{{ route('products.categories.update', [':id']) }}"; // Blade generates base URL
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

                // 4. Fill Form Fields
                form.querySelector('[name="name"]').value = rowData.name;
                form.querySelector('[name="description"]').value = rowData.description;
                form.querySelector('[id="is_active"]').checked = rowData.is_active;

                modalElement.classList.remove('hidden');
                modalElement.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            // --- DELETE Logic ---
            if (deleteBtn) {
                const rowData = JSON.parse(deleteBtn.closest('td').getAttribute('data-pass'));
                
                // 1. Change Modal Header
                modalTitle.innerText = 'Delete Category: ' + rowData.name;
                modalContent.innerHTML = `
                   <x-delete-category-form id="categoryForm" method="POST"/>
                `;
                const form = document.querySelector('#categoryForm');

                // 2. Change Form Action to Update URL (Assuming standard Laravel resource)
                const baseUrl = "{{ route('products.categories.destroy', [':id']) }}"; // Blade generates base URL
                const params = new URLSearchParams(@json(request()->query())).toString(); // JS
                const urlTemplate = params ? `${baseUrl}?${params}` : baseUrl;
                form.action = urlTemplate.replace(':id', rowData.id);

                modalElement.classList.remove('hidden');
                modalElement.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
        });
    });
</script>
@endpush