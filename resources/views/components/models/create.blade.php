@section('content-alert')
    <div class="row">
        <div class="col-md-8 col-md-offset-2 offset-md-2">
            @if (session('status'))
                @component(config('generator.view_component').'components.alert')
                    @slot('type', session('status-type'))
                    @if (session('status') instanceof \Illuminate\Support\HtmlString)
                        {!! session('status') !!}
                    @else
                        {{ session('status') }}
                    @endif
                @endcomponent
            @endif
        </div>
    </div>
@endsection

@section('panel-content')
    @foreach ($fields[$model_variable] as $key => $field)
        @component(config('generator.view_component').'components.fields.'.$field['field'], compact('field'))
        @endcomponent
    @endforeach
@endsection

@section('panel-footer')
    <div class="pull-right float-right">
        <button type="submit" name="redirect" value="{{ request()->filled('redirect') ? url(request()->redirect) : '' }}" class="btn btn-primary">
            {{ __('Store') }}
        </button>
        <button type="submit" name="redirect" value="{{ request()->fullUrl() }}" class="btn btn-primary">
            {{ __('Store') }} & {{ __('Create') }}
        </button>
    </div>
    @if(Route::has($resource_route.'.index') || request()->filled('redirect'))
        <a href="{{ request()->filled('redirect') ? url(request()->redirect) : route($resource_route.'.index') }}"
           class="btn btn-default btn-secondary">{{ __('Back') }}</a>
    @endif
@endsection

@section('content')
    @yield('content-alert')
    <div class="row">
        <div class="col-md-8 col-md-offset-2 offset-md-2">
            <form class="form"
                  action="{{ route($resource_route.'.store') }}"
                  method="POST"
                  @if (array_first($fields[$model_variable], function ($field) { return isset($field['type']) && $field['type'] == 'file'; } ))
                  enctype="multipart/form-data"
                  @endif
                >
                {{ csrf_field() }}
                @component(config('generator.view_component').'components.panel')
                    @slot('title')
                        {{ __('Create') }} {{ !empty($panel_title) ? $panel_title : ucwords(__($resource_route.'.singular')) }}
                    @endslot

                    @yield('panel-content')

                    @slot('footer')
                        @yield('panel-footer')
                    @endslot
                @endcomponent
            </form>
        </div>
    </div>
@endsection
