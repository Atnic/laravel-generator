@if (Route::has($resource_route.'.show'))
    @if ((auth()->check() && auth()->user()->can('view', [ $model, $parent ])) || auth()->guest())
        <a href="{{ route($resource_route.'.show', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
           class="btn btn-primary btn-sm btn-xs">{{ __('Show') }}</a>
    @endif
@endif
@if (Route::has($resource_route.'.edit'))
    @if ((auth()->check() && auth()->user()->can('update', [ $model, $parent ])) || auth()->guest())
        <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
           class="btn btn-success btn-sm btn-xs">{{ __('Edit') }}</a>
    @endif
@endif
@if (Route::has($resource_route.'.destroy'))
    @if ((auth()->check() && auth()->user()->can('delete', [ $model, $parent ])) || auth()->guest())
        <form style="display:inline"
              action="{{ route($resource_route.'.destroy', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
              method="POST"
              onsubmit="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
            {{ csrf_field() }}
            <button type="submit" class="btn btn-danger btn-sm btn-xs" name="_method"
                    value="DELETE">{{ __('Delete') }}</button>
        </form>
    @endif
@endif
