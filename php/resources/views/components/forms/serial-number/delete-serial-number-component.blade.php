<form {{$attributes->twMerge(['class' => 'my-2 flex flex-col gap-2'])}}>
    @csrf
    @method('DELETE')
    <p>Are you sure you want to delete this product info?</p>
    <div class="flex justify-end gap-2">
        <x-button variant="default" data-modal-close>Cancel</x-button>
        <x-button variant="danger" type="submit">Delete</x-button>
    </div>
</form>
