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
                <x-card-header class="flex flex-row py-3 px-4">Add Info</x-card-header>
                <form method="POST" class="mt-2 p-2" action="{{route('products.serial_number.store', $product->id)}}">
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
                    <div>
                        <x-button variant="success" type="submit">Save</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </article>
@endsection