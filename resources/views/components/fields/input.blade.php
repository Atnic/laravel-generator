<div class="form-group{{ $errors->has($field['name']) ? ' has-error' : '' }}">
    @if (!empty($field['type']) && $field['type'] == 'checkbox')
        <input type="hidden" name="{{ $field['name'] }}" value="{{ !empty($field['default_value']) ? $field['default_value'] : 0 }}">
        <div>
            <label>
                <input type="checkbox" name="{{ $field['name'] }}" class="i-checks"
                    value="{{ !empty($field['value']) ? $field['value'] : 1 }}"
                    {{ old($field['name'], isset($model) ? $model->{$field['name']} : request()->{$field['name']}) == (!empty($field['value']) ? $field['value'] : 0) ? 'checked' : '' }}
                    {{ !empty($field['readonly']) ? 'readonly' : '' }}
                    {{ !empty($field['disabled']) ? 'disabled' : '' }}
                    >
                {{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}
            </label>
        </div>
    @elseif (!empty($field['type']) && $field['type'] == 'radio')
        @foreach ($field['options'] as $key => $option)
            <div>
                <label>
                    <input type="radio" name="{{ $field['name'] }}" class="i-checks"
                        value="{{ $option['value'] }}" {{ old($field['name'], isset($model) ? $model->{$field['name']} : request()->{$field['name']}) == $option['value'] ? 'selected' : '' }}
                        {{ !empty($field['readonly']) ? 'readonly' : '' }}
                        {{ !empty($field['disabled']) ? 'disabled' : '' }}
                        > {{ $option['text'] }}
                </label>
            </div>
        @endforeach
    @else
        <label class="control-label">{{ !empty($field['label']) ? $field['label'] : ucwords(str_replace('_', ' ', snake_case($field['name']))) }}{{ !empty($field['required']) ? '*' : '' }}</label>
        <input type="{{ !empty($field['type']) ? $field['type'] : 'text' }}"
            class="form-control"
            title="{{ $field['name'] }}"
            name="{{ $field['name'] }}"
            value="{{ old($field['name'], isset($model) ? $model->{$field['name']} : request()->{$field['name']}) }}"
            @if (!empty($field['type']) && $field['type'] == 'number')
            step="{{ !empty($field['step']) ? $field['step'] : 'any' }}"
            @endif
            {{ !empty($field['required']) ? 'required' : '' }}
            {{ !empty($field['readonly']) ? 'readonly' : '' }}
            {{ !empty($field['disabled']) ? 'disabled' : '' }}
            >
    @endif
    @if ($errors->has($field['name']))
        <span class="help-block">{{ $errors->first($field['name']) }}</span>
    @endif
</div>
