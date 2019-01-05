@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            @if (session('status'))
                <div class="alert alert-{{ session('status-type') ? : 'success' }}">
                    {{ session('status') }}
                </div>
            @endif
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

                    @foreach ($fields[$model_variable] as $key => $field)
                        @component(config('generator.view_component').'components.fields.'.$field['field'], compact('field'))
                        @endcomponent
                    @endforeach

                    @slot('footer')
                        <div class="pull-right">
                            <button type="submit" name="redirect" value="{{ request()->filled('redirect') ? url(request()->redirect) : '' }}" class="btn btn-primary">
                                {{ __('Store') }}
                            </button>
                            <button type="submit" name="redirect" value="{{ request()->fullUrl() }}" class="btn btn-primary">
                                {{ __('Store') }} & {{ __('Create') }}
                            </button>
                        </div>
                        <a href="{{ request()->filled('redirect') ? url(request()->redirect) : route($resource_route.'.index') }}"
                           class="btn btn-default">{{ __('Back') }}</a>
                    @endslot
                @endcomponent
            </form>
        </div>
    </div>
@endsection
