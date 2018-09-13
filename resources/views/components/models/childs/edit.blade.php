@section('parent-content')
    <form class="form" action="{{ route($resource_route.'.update', [
        $parent->getKey(), $model->getKey(),
        'redirect' => route($resource_route.'.index', [
            $parent->getKey(),
            'redirect' => request()->filled('redirect') ? request()->redirect : null ]) ]) }}"
        method="POST"
        @if (array_first($fields[$model_variable], function ($field) { return isset($field['type']) && $field['type'] == 'file'; } ))
        enctype="multipart/form-data"
        @endif
        >
        {{ csrf_field() }}
        @foreach ($fields[$model_variable] as $key => $field)
            @component(config('generator.view_component').'components.fields.'.$field['field'], [
                'field' => $field,
                'model' => $model
            ])
            @endcomponent
        @endforeach
        <div class="pull-right">
            <button type="submit" name="_method" value="PUT" class="btn btn-primary">
                {{ __('Update') }}
            </button>
        </div>
        @if (Route::has($resource_route.'.index'))
            @auth
                @can('index', $model)
                    <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                       class="btn btn-default">{{ __('List') }}</a>
                @endcan
            @endauth
            @guest
                <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                   class="btn btn-default">{{ __('List') }}</a>
            @endguest
        @endif
        @if (Route::has($resource_route.'.destroy'))
            @auth
                @can('delete', $model)
                    <button type="submit" name="_method" value="DELETE" class="btn btn-danger"
                            onclick="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
                        {{ __('Delete') }}
                    </button>
                @endcan
            @endauth
            @guest
                <button type="submit" name="_method" value="DELETE" class="btn btn-danger"
                        onclick="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
                    {{ __('Delete') }}
                </button>
            @endguest
        @endif
    </form>
@endsection
