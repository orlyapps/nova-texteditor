@if (!$invoice)
    <div class="p-2 px-6 bg-gray-100 border-primary-500 mt-3" contenteditable="false" draggable="true" data-drag-handle>
        <div class="font-bold text-center">Rechnungspositonen</div>
    </div>
@else
    <table>
        <thead>
            <tr class="bg-gray-100">
                <th width="5%" class="px-1">
                    {{ __("Pos.") }}
                </th>
                <th>
                    {{ __("Beschreibung") }}
                </th>
                <th width="10%" class="text-right">
                    {{ __("Menge") }}
                </th>
                <th width="15%" class="text-right">
                    {{ __("Einzelpreis") }}
                </th>
                <th width="15%" class="text-right px-1">
                    {{ __("Gesamtpreis") }}
                </th>
            </tr>
        </thead>
        @foreach ($invoice->positions as $position)
            <tr>
                <td class="px-1"> {{ $loop->index + 1 }}</td>
                <td>
                    <div><strong>{{ $position->name }}</strong></div>
                    <div class="text-xs">{{ $position->description }}</div>
                </td>
                <td class="text-right">{{ $position->quantity }} </td>
                <td class="text-right ">
                    <x-money :amount="$position->subtotal * 100" currency="EUR"></x-money>
                </td>
                <td class="text-right  px-1">
                    <x-money :amount="$position->total * 100" currency="EUR"></x-money>
                </td>
            </tr>
        @endforeach
        <tfoot>
            <tr  class="bg-gray-100 text-xs">
                <td colspan="1"></td>
                <td class="font-bold">Gesamtbetrag netto</td>
                <td colspan="2"></td>
                <td class="text-right px-1">
                    <x-money :amount="$invoice->net_total * 100" currency="EUR"></x-money>
                </td>
            </tr>
             <tr class=" text-xs">
                <td colspan="1"></td>
                <td >zzgl. Umsatzsteuer 19%</td>
                <td colspan="2"></td>
                <td class="text-right px-1">
                    <x-money :amount="$invoice->tax * 100" currency="EUR"></x-money>
                </td>
            </tr>
             <tr  class="bg-gray-100">
                <td colspan="1"></td>
                <td class="font-bold">Gesamtbetrag brutto</td>
                <td colspan="2"></td>
                <td class="text-right px-1 font-bold">
                    <x-money :amount="$invoice->total * 100" currency="EUR"></x-money>
                </td>
            </tr>
        </tfoot>
    </table>
@endif
