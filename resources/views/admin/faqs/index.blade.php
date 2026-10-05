<x-layouts.admin :title="'FAQ'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Pertanyaan yang tampil di halaman <a href="{{ route('faq') }}" target="_blank" class="font-medium text-primary hover:underline">/faq</a>.</p>
        <x-button href="{{ route('admin.faqs.create') }}">Tambah FAQ</x-button>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl border border-border bg-card">
        @if ($faqs->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-muted-foreground">Belum ada FAQ.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-5 py-3 font-medium">#</th>
                            <th class="px-5 py-3 font-medium">Pertanyaan</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($faqs as $faq)
                            <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                                <td class="px-5 py-3 text-muted-foreground">{{ $faq->position }}</td>
                                <td class="px-5 py-3 font-medium">{{ $faq->question }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $faq->is_active ? 'bg-primary/15 text-primary' : 'bg-secondary text-muted-foreground' }}">
                                        {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.faqs.edit', $faq) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Hapus FAQ ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-destructive/40 px-2.5 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-5">
        {{ $faqs->links() }}
    </div>

</x-layouts.admin>
