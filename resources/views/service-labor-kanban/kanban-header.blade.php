
    {{ $record->{static::$recordTitleAttribute} }}



    <h3 class="mb-2 px-4 font-semibold text-lg text-gray-400">
    @if($record == '')Nenhum registro encontrado @else
    <span class="text-primary-400">❖</span>
    {{ $status['title'] }}
    @endif
</h3>

