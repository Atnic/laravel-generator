<div class="form-group{{ $errors->has($field['name']) ? ' has-error' : '' }}">
    @if (!empty($field['type']) && $field['type'] == 'checkbox')
        <input type="hidden" name="{{ $field['name'] }}" value="{{ !empty($field['default_value']) ? $field['default_value'] : 0 }}">
        <div class="form-check">
            <input type="checkbox" name="{{ $field['name'] }}" class="form-check-input i-checks {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
                   value="{{ !empty($field['value']) ? $field['value'] : 1 }}"
                    {{ old($field['name'], isset($model) ? $model->{$field['name']} : request()->{$field['name']}) == (!empty($field['value']) ? $field['value'] : 0) ? 'checked' : '' }}
                    {{ !empty($field['readonly']) ? 'readonly' : '' }}
                    {{ !empty($field['disabled']) ? 'disabled' : '' }}
                  title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}">
            <label class="form-check-label">
                {{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}
            </label>
            @if ($errors->has($field['name']))
                <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
            @endif
        </div>
    @elseif (!empty($field['type']) && $field['type'] == 'radio')
        @foreach ($field['options'] as $key => $option)
            <div class="form-check">
                <input type="radio" name="{{ $field['name'] }}" class="form-check-input i-checks {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
                       value="{{ $option['value'] }}" {{ old($field['name'], isset($model) ? $model->{$field['name']} : request()->{$field['name']}) == $option['value'] ? 'selected' : '' }}
                        {{ !empty($field['readonly']) ? 'readonly' : '' }}
                        {{ !empty($field['disabled']) ? 'disabled' : '' }}
                       title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}">
                <label class="form-check-label" for="exampleRadios1">
                    {{ $option['text'] }}
                </label>
                @if ($loop->last && $errors->has($field['name']))
                    <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
                @endif
            </div>
        @endforeach
    @else
        <label class="control-label">{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}</label>
        <input type="{{ !empty($field['type']) ? $field['type'] : 'text' }}"
            class="form-control {{ !empty($field['type']) && $field['type'] == 'file' ? 'form-control-file' : '' }} {{ $errors->has($field['name']) ? 'is-invalid' : '' }}"
            title="{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}"
            name="{{ $field['name'] }}"
            value="{{ old($field['name'], isset($model) ? $model->{$field['name']} : request()->{$field['name']}) }}"
            @if (!empty($field['type']) && $field['type'] == 'number')
            step="{{ !empty($field['step']) ? $field['step'] : 'any' }}"
            @endif
            {{ !empty($field['required']) ? 'required' : '' }}
            {{ !empty($field['readonly']) ? 'readonly' : '' }}
            {{ !empty($field['disabled']) ? 'disabled' : '' }}
            >
        @if ($errors->has($field['name']))
            <span class="help-block invalid-feedback">{{ $errors->first($field['name']) }}</span>
        @endif
    @endif
</div>
