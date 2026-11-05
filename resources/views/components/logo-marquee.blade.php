@props(['items' => [], 'label' => 'Sudah Dipercaya Berbagai Instansi, Industri, Sekolah & Komunitas'])

@if (count($items))
    <section class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8" aria-label="Dipercaya oleh">
        <p class="mb-5 text-center text-xs font-semibold uppercase tracking-widest text-muted-foreground">
            {{ $label }}
        </p>

        {{-- Dua salinan identik dalam satu track: translateX(-50%) kembali persis
             ke posisi awal sehingga loop mulus tanpa jeda. Hover menjeda. --}}
        <div class="relative overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
            <div class="animate-marquee flex w-max">
                @foreach ([0, 1] as $copy)
                    <div class="flex shrink-0 gap-3 pr-3 sm:gap-4 sm:pr-4" @if ($copy === 1) aria-hidden="true" @endif>
                        @foreach ($items as $item)
                            <div class="flex h-14 w-[150px] shrink-0 items-center justify-center gap-2.5 rounded-xl border border-border bg-card px-3.5 sm:h-16 sm:w-[180px] sm:gap-3 sm:px-4">
                                @if (! empty($item['image']))
                                    <img
                                        src="{{ asset($item['image']) }}"
                                        alt="{{ $item['name'] }}"
                                        loading="lazy"
                                        decoding="async"
                                        width="180"
                                        height="64"
                                        class="max-h-9 w-auto max-w-full object-contain sm:max-h-10"
                                    />
                                @else
                                    @php
                                        $words = array_values(array_filter(
                                            preg_split('/\s+/', trim($item['name'])),
                                            static fn ($w) => mb_strlen($w) > 1
                                        ));
                                        $initials = mb_strtoupper(
                                            mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1)
                                        );
                                    @endphp
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                        {{ $initials }}
                                    </span>
                                    <span class="line-clamp-2 text-left text-[11px] font-semibold uppercase leading-tight tracking-wide text-foreground/80 sm:text-xs">
                                        {{ $item['name'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
