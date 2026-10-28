<x-layouts.admin :title="'Dashboard'">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Menunggu Konfirmasi</p>
            <p class="mt-2 text-2xl font-bold text-primary sm:text-3xl">{{ $stats['pending'] }}</p>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="mt-1.5 inline-block py-1.5 text-xs font-medium text-primary hover:underline">Lihat leads →</a>
        </div>
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Sedang Diproses</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ $stats['production'] }}</p>
            <a href="{{ route('admin.orders.index', ['status' => 'production']) }}" class="mt-1.5 inline-block py-1.5 text-xs font-medium text-primary hover:underline">Lihat pesanan →</a>
        </div>
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Selesai</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ $stats['completed'] }}</p>
            <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="mt-1.5 inline-block py-1.5 text-xs font-medium text-primary hover:underline">Lihat arsip →</a>
        </div>
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Pesanan Bulan Ini</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ $stats['month'] }}</p>
            <p class="mt-2 text-xs text-muted-foreground">Total semua: {{ $stats['orders'] }}</p>
        </div>
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Produk</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ $stats['products'] }}</p>
            <a href="{{ route('admin.products.index') }}" class="mt-1.5 inline-block py-1.5 text-xs font-medium text-primary hover:underline">Kelola produk →</a>
        </div>
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Kategori</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ $stats['categories'] }}</p>
            <a href="{{ route('admin.categories.index') }}" class="mt-1.5 inline-block py-1.5 text-xs font-medium text-primary hover:underline">Kelola kategori →</a>
        </div>
        <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
            <p class="text-sm font-medium text-muted-foreground">Dibatalkan</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ $stats['cancelled'] }}</p>
            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="mt-1.5 inline-block py-1.5 text-xs font-medium text-primary hover:underline">Lihat pesanan →</a>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-border bg-card p-5">
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
            <div>
                <h2 class="text-base font-semibold">Pesanan 30 Hari Terakhir</h2>
                <p class="mt-0.5 text-xs text-muted-foreground">Jumlah pesanan masuk per hari.</p>
            </div>
            <span class="text-xs text-muted-foreground">{{ $chart[0]['label'] }} – {{ $chart[29]['label'] }}</span>
        </div>
        <div class="mt-4 flex h-32 items-end gap-[3px] border-b border-border" role="img" aria-label="Grafik batang jumlah pesanan masuk per hari selama 30 hari terakhir">
            @foreach ($chart as $day)
                <div class="group flex h-full flex-1 items-end" title="{{ $day['label'] }}: {{ $day['count'] }} pesanan">
                    <div
                        class="w-full rounded-t-sm bg-primary/70 transition-colors group-hover:bg-primary"
                        style="height: {{ $day['count'] > 0 ? max(4, round($day['count'] / $chartMax * 100)) : 0 }}%"
                    ></div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex justify-between text-[10px] text-muted-foreground">
            <span>{{ $chart[0]['label'] }}</span>
            <span>{{ $chart[14]['label'] }}</span>
            <span>{{ $chart[29]['label'] }}</span>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-border bg-card">
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h2 class="text-sm font-semibold">Pesanan Terbaru</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-medium text-primary hover:underline">Semua pesanan →</a>
        </div>

        @if ($recentOrders->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-muted-foreground">Belum ada pesanan masuk.</p>
        @else
            <ul class="divide-y divide-border lg:hidden">
                @foreach ($recentOrders as $order)
                    <li>
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-start justify-between gap-3 px-5 py-3 hover:bg-secondary/50">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-primary">#{{ $order->id }}</span>
                                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $order->statusLabel() }}</span>
                                </div>
                                <p class="mt-1 truncate text-sm font-medium">{{ $order->name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ $order->product?->title ?? 'Custom' }} · {{ $order->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-1 h-4 w-4 shrink-0 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-5 py-3 font-medium">#</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                            <th class="px-5 py-3 font-medium">Produk</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-primary hover:underline">#{{ $order->id }}</a>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-medium">{{ $order->name }}</span>
                                    <span class="block text-xs text-muted-foreground">{{ $order->whatsapp_number }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $order->product?->title ?? 'Custom' }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-layouts.admin>
