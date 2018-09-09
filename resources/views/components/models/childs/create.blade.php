@section('parent-content')
    <form class="form" action="{{ route($resource_route.'.store', [ $parent->getKey() ]) }}" method="POST">
        {{ csrf_field() }}
        @foreach ($fields[$model_variable] as $key => $field)
            @component(config('generator.view_component').'components.fields.'.$field['field'], compact('field'))
            @endcomponent
        @endforeach
        <div class="pull-right">
            <button type="submit" name="redirect" value="{{ route($resource_route.'.index', [ $parent->getKey(),
                'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" class="btn btn-primary">
                {{ __('Store') }}
            </button>
            <button type="submit" name="redirect" value="{{ request()->fullUrl() }}" class="btn btn-primary">
                {{ __('Store') }} & {{ __('Create') }}
            </button>
        </div>
        @if (Route::has($resource_route.'.index'))
            <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
               class="btn btn-default">{{ __('List') }}</a>
        @endif
    </form>
@endsection
