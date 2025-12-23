@props(['label','text','model','validation','ignore','flex','copyto','position','live','classMain','class','placeholder','labelfont','customValidation'])
<div class="{{!empty($classMain) ? $classMain : ''}} pb-2.5">
    <div @if (isset($ignore)) wire:ignore @endif :class="{'flex items-center': @js(isset($flex)) }" class="{{isset($class) ? $class : ''}}">
        <label for="{{$label}}" :class="{'w-32': @js(isset($flex)) }" class="block font-medium text-sm text-gray-900 dark:text-white {{isset($labelfont) ? $labelfont : ''}}">{{$text}}</label>
        @if (isset($live))
        <input id="{{$label}}" placeholder="<?= !empty($placeholder) ? $placeholder : '' ?>" wire:model.{{$live}}="{{$model}}" @if (isset($copyto)) copyto="{{$copyto}}" @endif :class="{'copy': @js(isset($copyto)) }" class="{{isset($position) ? $position : ''}} bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
        @else
        <input id="{{$label}}" placeholder="<?= !empty($placeholder) ? $placeholder : '' ?>" wire:model="{{$model}}" @if (isset($copyto)) copyto="{{$copyto}}" @endif :class="{'copy': @js(isset($copyto)) }" class="{{isset($position) ? $position : ''}} bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
        @endif
        
    </div>
    <?php if (!empty($customValidation)) { ?>
        <span class="<?= $label ?> text-red-500 hidden error"></span>
    <?php } ?>

    <?php if (!empty($validation)) {?>
            @error($model)
            <span class="text-red-500">{{$message}}</span>
            @enderror
    <?php } ?>
</div>