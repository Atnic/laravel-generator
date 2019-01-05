@if (Route::has($resource_route.'.show'))
    @if ((auth()->check() && auth()->user()->can('view', $model)) || auth()->guest())
        <a href="{{ route($resource_route.'.show', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
           class="btn btn-primary btn-xs">{{ __('Show') }}</a>
    @endif
@endif
@if (Route::has($resource_route.'.edit'))
    @if ((auth()->check() && auth()->user()->can('update', $model)) || auth()->guest())
        <a href="{{ route($resource_route.'.edit', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
           class="btn btn-success btn-xs">{{ __('Edit') }}</a>
    @endif
@endif
@if (Route::has($resource_route.'.destroy'))
    @if ((auth()->check() && auth()->user()->can('delete', $model)) || auth()->guest())
        <form style="display:inline"
              action="{{ route($resource_route.'.destroy', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
              method="POST"
              onsubmit="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
            {{ csrf_field() }}
            <button type="submit" class="btn btn-danger btn-xs" name="_method"
                    value="DELETE">{{ __('Delete') }}</button>
        </form>
    @endif
@endif
