@php
    $names = explode('.', $field['name']);
    $name = array_shift($names).(count($names) > 0 ? '['.collect($names)->map(function ($name) { return $name == '*' ? '' : $name; })->implode('][').']' : '');
@endphp

<div class="form-group{{ $errors->has($field['name']) ? ' has-error' : '' }}">
    @if (!empty($field['type']) && $field['type'] == 'checkbox')
        <input type="hidden" name="{{ $name }}" value="{{ !empty($field['default_value']) ? $field['default_value'] : 0 }}" {{ isset($field['disabled']) && $field['disabled'] ? 'disabled' : '' }}>
        <div class="form-check">
            <input id="{{ $field['name'] }}" type="checkbox" name="{{ $name }}" class="form-check-input i-checks {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
                   value="{{ !empty($field['value']) ? $field['value'] : 1 }}"
                    {{ old($field['name'], isset($model) ? data_get($model, $field['name']) : data_get(request(), $field['name'], isset($field['value']) ? $field['value'] : null)) == (!empty($field['value']) ? $field['value'] : 1) ? 'checked' : '' }}
                    {{ !empty($field['readonly']) ? 'readonly' : '' }}
                    {{ !empty($field['disabled']) ? 'disabled' : '' }}
                  title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}">
            <label class="form-check-label" for="{{ $field['name'] }}">
                {{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}
            </label>
            @if ($errors->has($field['name']))
                <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
            @endif
        </div>
    @elseif (!empty($field['type']) && $field['type'] == 'radio')
        <label class="control-label" for="{{ $field['name'] }}">{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}</label>
        @foreach ($field['options'] ?? [] as $key => $option)
            <div class="form-check">
                <input id="{{ $field['name'] }}" type="radio" name="{{ $name }}" class="form-check-input i-checks {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
                       value="{{ $option['value'] }}" {{ old($field['name'], isset($model) ? data_get($model, $field['name']) : data_get(request(), $field['name'], isset($field['value']) ? $field['value'] : null)) == $option['value'] ? 'selected' : '' }}
                        {{ !empty($field['readonly']) ? 'readonly' : '' }}
                        {{ !empty($field['disabled']) ? 'disabled' : '' }}
                       title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}">
                <label class="form-check-label" for="{{ $field['name'] }}">
                    {{ $option['text'] }}
                </label>
                @if ($loop->last && $errors->has($field['name']))
                    <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
                @endif
            </div>
        @endforeach
    @elseif (!empty($field['type']) && $field['type'] == 'file')
        <label class="control-label">
            {{ !empty($field['label']) ? $field['label'] : title_case(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}
            @if(!empty($field['template']))
                (<a href="{{ $field['template'] }}" target="_blank">{{ __('Template') }}</a>)
            @endif
        </label>
        @if (Storage::exists(old($field['name'], isset($model) ? data_get($model, $field['name']) : request()->{$field['name']})) ||
             Storage::cloud()->exists(old($field['name'], isset($model) ? data_get($model, $field['name']) : request()->{$field['name']})))
            <div class="form-check">
                <input id="{{ $field['name'] }}" type="checkbox" name="{{ $name }}" class="form-check-input i-checks {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
                       value="{{ !empty($field['value']) ? $field['value'] : 1 }}"
                       {{ old($field['name'], isset($model) ? data_get($model, $field['name']) : data_get(request(), $field['name'], isset($field['value']) ? $field['value'] : null)) == (!empty($field['value']) ? $field['value'] : 1) ? 'checked' : '' }}
                       {{ !empty($field['readonly']) ? 'readonly' : '' }}
                       {{ !empty($field['disabled']) ? 'disabled' : '' }}
                       title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}">
                <label class="form-check-label" for="{{ $field['name'] }}">
                    @if (Storage::exists(old($field['name'], isset($model) ? data_get($model, $field['name']) : request()->{$field['name']})))
                        <a href="{{ Storage::url(old($field['name'], isset($model) ? data_get($model, $field['name']) : request()->{$field['name']})) }}" target="_blank">
                            {{ !empty($field['label']) ? $field['label'] : title_case(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}
                        </a>
                    @elseif (Storage::cloud()->exists(old($field['name'], isset($model) ? data_get($model, $field['name']) : request()->{$field['name']})))
                        <a href="{{ Storage::cloud()->url(old($field['name'], isset($model) ? data_get($model, $field['name']) : request()->{$field['name']})) }}" target="_blank">
                            {{ !empty($field['label']) ? $field['label'] : title_case(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}
                        </a>
                    @endif
                </label>
            </div>
        @endif
        <input type="file"
               class="form-control"
               name="{{ $name }}"
                {{ !empty($field['required']) ? 'required' : '' }}
                {{ !empty($field['disabled']) ? 'disabled' : '' }}>
        @if ($errors->has($field['name']))
          <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
        @endif
    @elseif (!empty($field['type']) && $field['type'] == 'hidden')
        <input type="hidden" name="{{ $name }}" value="{{ old($field['name'], isset($model) ? data_get($model, $field['name'], $field['value']) : request()->input($field['name'], $field['value'])) }}" {{ isset($field['disabled']) && $field['disabled'] ? 'disabled' : '' }}>
    @else
        <label class="control-label" for="{{ $field['name'] }}">{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}</label>
        <input id="{{ $field['name'] }}" type="{{ !empty($field['type']) ? $field['type'] : 'text' }}"
            class="{{ !empty($field['type']) && $field['type'] == 'file' ? 'form-control-file' : 'form-control' }} {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
            title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}"
            name="{{ $name }}"
            @if (empty($field['type']) || $field['type'] != 'file')
            value="{{ old($field['name'], isset($model) ? data_get($model, $field['name']) : data_get(request(), $field['name'], isset($field['value']) ? $field['value'] : null)) }}"
            @endif
            @if (!empty($field['type']) && $field['type'] == 'number')
            step="{{ !empty($field['step']) ? $field['step'] : 'any' }}"
            @endif
            @isset($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endisset
            {{ !empty($field['required']) ? 'required' : '' }}
            {{ !empty($field['readonly']) ? 'readonly' : '' }}
            {{ !empty($field['disabled']) ? 'disabled' : '' }}
            >
        @if ($errors->has($field['name']))
            <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
        @endif
    @endif
</div>
