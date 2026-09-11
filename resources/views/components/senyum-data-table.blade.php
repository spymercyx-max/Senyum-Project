@props(['headers' => [], 'note' => 'Geser ke samping untuk melihat seluruh kolom pada layar kecil.'])

<div {{ $attributes }}>
    <div class="sn-table-wrap">
        <table class="sn-table">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if ($note)
        <p class="sn-table-note">{{ $note }}</p>
    @endif
</div>
