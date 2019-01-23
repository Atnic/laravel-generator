@php
    $names = explode('.', $field['name']);
    $name = array_shift($names).(count($names) > 0 ? '['.collect($names)->map(function ($name) { return $name == '*' ? $name : ''; })->implode('][').']' : '');
@endphp

<div class="form-group{{ $errors->has($field['name']) ? ' has-error' : '' }}">
    <label class="control-label" for="{{ $field['name'] }}">{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}</label>
    <select id="{{ $field['name'] }}" class="form-control{{ $errors->has($field['name']) ? ' is-invalid' : '' }}" name="{{ $field['name'] }}"
        title="{{ $field['name'] }}"
        {{ !empty($field['readonly']) ? 'readonly' : '' }}
        {{ !empty($field['disabled']) ? 'disabled' : '' }}
        {{ !empty($field['required']) ? 'required' : '' }}
        >
        @foreach ($field['options'] as $option)
            <option value="{{ $option['value'] }}" {{ $option['value'] == old($field['name'], isset($model) ? data_get($model, $field['name']) : data_get(request(), $field['name'])) ? 'selected' : '' }}>{{ $option['text'] }}</option>
        @endforeach
    </select>
    @if ($errors->has($field['name']))
        <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
    @endif
</div>
