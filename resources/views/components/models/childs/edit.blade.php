@section('parent-content-footer')
    <div class="pull-right float-right">
        <button type="submit" name="_method" value="PUT" class="btn btn-primary">
            {{ __('Update') }}
        </button>
    </div>
    @if (Route::has($resource_route.'.index'))
        @if ((auth()->check() && auth()->user()->can('index', $model)) || auth()->guest())
            <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
               class="btn btn-default btn-secondary">{{ __('List') }}</a>
        @endif
    @endif
    @if (Route::has($resource_route.'.destroy'))
        @if ((auth()->check() && auth()->user()->can('delete', $model)) || auth()->guest())
            <button type="submit" name="_method" value="DELETE" class="btn btn-danger"
                    onclick="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
                {{ __('Delete') }}
            </button>
        @endif
    @endif
@endsection

@section('parent-content-content')
    @foreach ($fields[$model_variable] as $key => $field)
        @component(config('generator.view_component').'components.fields.'.$field['field'], [
            'field' => $field,
            'model' => $model
        ])
        @endcomponent
    @endforeach
@endsection

@section('parent-content')
    <form class="form" action="{{ route($resource_route.'.update', [
        $parent->getKey(), $model->getKey(),
        'redirect' => route($resource_route.'.index', [
            $parent->getKey(),
            'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) ]) }}"
        method="POST"
        @if (array_first($fields[$model_variable], function ($field) { return isset($field['type']) && $field['type'] == 'file'; } ))
        enctype="multipart/form-data"
        @endif
        >
        {{ csrf_field() }}

        @yield('parent-content-content')

        @yield('parent-content-footer')
    </form>
@endsection
