@section('panel-content')
    @foreach ($fields[$model_variable] as $key => $field)
        @component(config('generator.view_component').'components.fields.'.$field['field'], [
            'field' => $field,
            'model' => $model
        ])
        @endcomponent
    @endforeach
@endsection

@section('panel-footer')
    <div class="pull-right">
        <button type="submit" name="_method" value="PUT" class="btn btn-primary">
            {{ __('Update') }}
        </button>
    </div>
    <a href="{{ request()->filled('redirect') ? url(request()->redirect) : route($resource_route.'.index') }}"
       class="btn btn-default">{{{ __('Back') }}}</a>
    @if (Route::has($resource_route.'.destroy'))
        @if ((auth()->check() && auth()->user()->can('delete', $model)) || auth()->guest())
            <button type="submit" name="_method" value="DELETE" class="btn btn-danger"
                    onclick="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
                {{ __('Delete') }}
            </button>
        @endif
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
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
            <form class="form"
                  action="{{ route($resource_route.'.update', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                  method="POST"
                  @if (array_first($fields[$model_variable], function ($field) { return isset($field['type']) && $field['type'] == 'file'; } ))
                  enctype="multipart/form-data"
                  @endif
                >
                {{ csrf_field() }}
                @component(config('generator.view_component').'components.panel')
                    @slot('title')
                        {{  __('Edit') }} {{ !empty($panel_title) ? $panel_title : ucwords(__($resource_route.'.singular')) }}
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
