@php
    $filter = 'App\\Filters\\'.class_basename($model_class).'Filter';
@endphp
@if(in_array($column['name'], collect((new $filter)->getSearchables())->map(function ($searchable, $key) { return is_array($searchable) ? $key : $searchable; })->values()->toArray()))
<input class="form-control" name="{{ $column['name'] }}" value="{{ request()->{$column['name']} }}" placeholder="{{ __('All') }}" form="search">
@endif
