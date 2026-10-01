@props(['funnel'])

<div class="neu-card p-4 sm:p-6 mb-6 overflow-x-auto">
    <h2 class="font-semibold mb-1">Funnel: Status Follow Up &rarr; Rencana Wisuda</h2>
    <p class="text-xs text-gray-400 mb-4">
        Persentase di tiap baris dihitung dari total baris itu sendiri, bukan dari keseluruhan data —
        menjawab pertanyaan "dari yang berstatus ini, berapa persen memilih rencana wisuda X?".
    </p>

    @if ($funnel['baris']->sum('total') === 0)
        <p class="text-sm text-gray-400">Belum ada data Status Follow Up yang diisi untuk filter ini.</p>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-3 py-2 text-left">Status Follow Up</th>
                    <th class="px-3 py-2 text-left">Total</th>
                    @foreach ($funnel['kolomLabels'] as $kolom)
                        <th class="px-3 py-2 text-left whitespace-nowrap">{{ $kolom }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($funnel['baris'] as $baris)
                    <tr>
                        <td class="px-3 py-2 font-medium whitespace-nowrap">{{ $baris['nama'] }}</td>
                        <td class="px-3 py-2">{{ $baris['total'] }}</td>
                        @foreach ($baris['sel'] as $sel)
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if ($baris['total'] > 0)
                                    {{ $sel['jumlah'] }} <span class="text-gray-400">({{ $sel['persen'] }}%)</span>
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
