<div>
@if(isset($content))
    <div class="text-sm text-gray-800">
        {!! $content !!}
    </div>
@else
    <div class="text-sm text-gray-500 italic">
        Sem informações disponíveis
    </div>
@endif
</div>