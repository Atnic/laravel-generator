@section('parent-content-footer')
    <div class="pull-right float-right">
        <button type="submit" name="redirect" value="{{ route($resource_route.'.index', [ $parent->getKey(),
                'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}" class="btn btn-primary">
            {{ __('Store') }}
        </button>
        <button type="submit" name="redirect" value="{{ request()->fullUrl() }}" class="btn btn-primary">
            {{ __('Store') }} & {{ __('Create') }}
        </button>
    </div>
    @if (Route::has($resource_route.'.index'))
        <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
           class="btn btn-default btn-secondary">{{ __('List') }}</a>
    @endif
@endsection

@section('parent-content-content')
    @foreach ($fields[$model_variable] as $key => $field)
        @component(config('generator.view_component').'components.fields.'.$field['field'], compact('field'))
        @endcomponent
    @endforeach
@endsection

@section('parent-content')
    <form class="form" action="{{ route($resource_route.'.store', [ $parent->getKey() ]) }}"
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
