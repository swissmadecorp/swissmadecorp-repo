@props(['label','text','model','iterators','validation','ignore','extraoption'])

<div @if (isset($ignore)) wire:ignore @endif>
    <label for="{{$label}}" class="block text-sm font-medium text-gray-900 dark:text-white">{{$text}}</label>
    <select id="{{$label}}" wire:model="{{$model}}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <?php if (isset($extraoption)) { ?>
            <option value="-1"></option>
        <?php } ?>
        
        @foreach ($iterators as $key => $iterator)
                <?php if (strpos($iterator,":")) {
                    $spl = explode(':',$iterator) ?>
                    <option value="{{ $spl[0] }}">{{ str_replace('\/','/', $spl[0]) }}</option>
                <?php } else {?>
                    <option value="{{ $key }}">{{ str_replace('\/','/', $iterator) }}</option>
                <?php } ?>
        @endforeach
    </select>
    @if (isset($validation))
        @error($model)
        <span class="text-red-500">{{$message}}</span>
        @enderror
    @endif
</div>

<!-- checks delux promo code mx473 -->