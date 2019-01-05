<div class="panel panel-default card">
    @if(isset($header) || isset($title))
        <div class="panel-heading card-header">
            @if(isset($header))
                {{ $header }}
            @else
                <span class="panel-title">{{ $title }}</span>
                @isset($tools)
                    <div class="pull-right float-right">
                        {{ $tools }}
                    </div>
                @endisset
            @endif
        </div>
    @endif
    <div class="panel-body card-body">
        {{ !empty($body) ? $body : $slot }}
    </div>
    @isset($footer)
        <div class="panel-footer card-footer">
            {{ $footer }}
        </div>
    @endisset
</div>
