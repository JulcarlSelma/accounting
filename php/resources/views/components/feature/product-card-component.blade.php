@props([
    'product' => null,
    'isShowPrice' => false,
    'isShowTrash' => false,
    'trashClassContainer' => '',
    'trashClassIcon' => 'cursor-pointer w-4 h-4',
    'idToDelete' => null
])
<x-card {{$attributes->twMerge(['class' => 'relative h-fit w-full rounded-md p-2 flex flex-col gap-2'])}}>
    <div class="bg-[#45b9f2] w-full h-fit rounded-md relative overflow-hidden">
        <img src="{{asset($product->logo_path ?? '/images/default-avatar.png')}}" class="object-cover w-full h-auto"/>
    </div>
    <div class="flex flex-row justify-between">
        <div class="flex flex-col min-w-0">
            <small class="truncate">{{$product->name}}</small>
            <small class="text-xs text-gray-400">{{$product->brand ? $product->brand->name : ''}}</small>
            <small class="text-xs">{{$product->unitDisplayText}}</small>
        </div>
        @if($isShowTrash)
            <div class="{{$trashClassContainer}}">
                <x-trash-icon class="deleteActionButton {{$trashClassIcon}}" data-id="{{$idToDelete}}" data-name="{{$product ? $product->name : ''}}" />
            </div>
        @endif
    </div>
</x-card>
