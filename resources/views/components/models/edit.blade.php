@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            @if (session('status'))
                <div class="alert alert-{{ session('status-type') ? : 'success' }}">
                    {{ session('status') }}
                </div>
            @endif
            <form class="form"
                  action="{{ route($resource_route.'.update', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                  method="POST"
                  @if (array_first($fields[$model_variable], function ($field) { return isset($field['type']) && $field['type'] == 'file'; } ))
                  enctype="multipart/form-data"
                  @endif
                >
                {{ csrf_field() }}
                @component(config('generator.view_component').'components.panel')
                    @slot('title')
                        {{  __('Edit') }} {{ !empty($panel_title) ? $panel_title : title_case(__($resource_route.'.singular')) }}
                    @endslot

                    @foreach ($fields[$model_variable] as $key => $field)
                        @component(config('generator.view_component').'components.fields.'.$field['field'], [
                            'field' => $field,
                            'model' => $model
                        ])
                        @endcomponent
                    @endforeach

                    @slot('footer')
                        <div class="pull-right">
                            <button type="submit" name="_method" value="PUT" class="btn btn-primary">
                                {{ __('Update') }}
                            </button>
                        </div>
                        <a href="{{ request()->filled('redirect') ? request()->redirect : route($resource_route.'.index') }}"
                           class="btn btn-default">{{{ __('Back') }}}</a>
                        @if (Route::has($resource_route.'.destroy'))
                            <button type="submit" name="_method" value="DELETE" class="btn btn-danger"
                                    onclick="return confirm('{{ __('Are you sure you want to delete?') }}');">
                                {{ __('Delete') }}
                            </button>
                        @endif
                    @endslot
                @endcomponent
            </form>
        </div>
    </div>
@endsection
