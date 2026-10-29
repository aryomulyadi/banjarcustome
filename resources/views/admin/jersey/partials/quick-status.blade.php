{{-- Chip status + popover quick-edit (inline, tanpa reload).
     Dipakai baris tabel & kartu — scope Alpine per pemakaian, jadi x-ref
     boleh sama antar baris. Posisi popover fixed agar tidak terpotong
     container scroll tabel. --}}
@php($entry = $entry ?? null)

<div
    class="relative inline-block text-left"
    x-data="{ open: false, pos: { top: '0px', left: '0px' } }"
    @click.outside="open = false"
>
    <button
        type="button"
        aria-haspopup="true"
        :aria-expanded="open"
        title="Ubah status"
        class="rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        @click="open = !open; if (open) { const r = $el.getBoundingClientRect(); pos = { top: Math.min(r.bottom + 4, Math.max(8, window.innerHeight - 300)) + 'px', left: Math.min(r.left, window.innerWidth - 200) + 'px' }; }"
    >
        <x-status-chip :status="$entry->status" x-ref="rowChip" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.origin.top.left
        :style="'position: fixed; z-index: 50; top: ' + pos.top + '; left: ' + pos.left"
        class="w-48 rounded-md border border-border bg-popover p-1.5 shadow-lg"
        role="menu"
    >
        @foreach (\App\Models\JerseyData::STATUSES as $option)
            <button
                type="button"
                role="menuitem"
                data-url="{{ route('admin.jersey.status', $entry) }}"
                data-value="{{ $option }}"
                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-secondary {{ $option === $entry->status ? 'font-semibold' : '' }}"
                @click="const chip = $refs.rowChip; const src = $el.querySelector('[data-swatch]'); const token = document.querySelector('#bulk-jersey input[name=_token]').value; fetch($el.dataset.url, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ status: $el.dataset.value }) }).then(function (res) { if (!res.ok) throw new Error('status gagal'); return res.json(); }).then(function (data) { chip.innerText = data.status; chip.className = src.className; open = false; }).catch(function () { window.location.reload(); });"
            >
                <x-status-chip :status="$option" data-swatch />
            </button>
        @endforeach
    </div>
</div>
