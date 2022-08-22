@if (!$invoice)
    <div class="p-2 px-6 bg-gray-100 border-primary-500 mt-3" contenteditable="false" draggable="true" data-drag-handle>
        <div class="font-bold text-center">Rechnungspositonen</div>
    </div>
@else
    <table>
        <tr>
            <th width="5%">Pos</th>
            <th>Beschreibung</th>
            <th width="10%">Menge</th>
            <th width="15%">Einzelpreis</th>
            <th width="15%">Gesamtpreis</th>
        </tr>
        @foreach ($invoice->positions as $position)
            <tr>
                <td> {{ $loop->index + 1 }}</td>
                <td>
                    <div><strong>{{ $position->name }}</strong></div>
                    <div class="text-sm">{{ $position->description }}</div>
                </td>
                <td>{{ $position->quantity }} </td>
                <td>{{ $position->subtotal }} </td>
                <td>{{ $position->total }} </td>
            </tr>
        @endforeach#
    </table>
@endif
