<x-layouts.admin :title="'Testimoni'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Testimoni pelanggan tampil sebagai slider di beranda. Isi dengan testimoni asli pelanggan Anda.</p>
        <x-button href="{{ route('admin.testimonials.create') }}">Tambah Testimoni</x-button>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl border border-border bg-card">
        @if ($testimonials->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-muted-foreground">Belum ada testimoni.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Asal</th>
                            <th class="px-5 py-3 font-medium">Rating</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($testimonials as $testimonial)
                            <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                                <td class="px-5 py-3">
                                    <span class="block font-medium">{{ $testimonial->name }}</span>
                                    <span class="block max-w-xs truncate text-xs text-muted-foreground">{{ $testimonial->content }}</span>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">{{ collect([$testimonial->role, $testimonial->city])->filter()->implode(', ') ?: '—' }}</td>
                                <td class="px-5 py-3">{{ $testimonial->rating }}/5</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $testimonial->is_active ? 'bg-primary/15 text-primary' : 'bg-secondary text-muted-foreground' }}">
                                        {{ $testimonial->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Hapus testimoni ini?')">
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
        {{ $testimonials->links() }}
    </div>

</x-layouts.admin>
