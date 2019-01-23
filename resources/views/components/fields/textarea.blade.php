@php
    $names = explode('.', $field['name']);
    $name = array_shift($names).(count($names) > 0 ? '['.collect($names)->map(function ($name) { return $name == '*' ? $name : ''; })->implode('][').']' : '');
@endphp

<div class="form-group{{ $errors->has($field['name']) ? ' has-error' : '' }}">
    <label class="control-label" for="{{ $field['name'] }}">{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}</label>
    <textarea id="{{ $field['name'] }}" class="form-control{{ $errors->has($field['name']) ? ' is-invalid' : '' }}" name="{{ $field['name'] }}"
        @isset($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endisset
        rows="{{ !empty($field['rows']) ? $field['rows'] : 4 }}"
        cols="{{ !empty($field['cols']) ? $field['cols'] : 80 }}"
        {{ !empty($field['readonly']) ? 'readonly' : '' }}
        {{ !empty($field['disabled']) ? 'disabled' : '' }}
        {{ !empty($field['required']) ? 'required' : '' }}
        >{{ old($field['name'], isset($model) ? data_get($model, $field['name']) : data_get(request(), $field['name'])) }}</textarea>
    @if ($errors->has($field['name']))
        <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
    @endif
</div>
