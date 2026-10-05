@php($faq = $faq ?? null)

<div class="max-w-xl space-y-5">
    <div class="space-y-2">
        <x-label for="question">Pertanyaan *</x-label>
        <x-input id="question" name="question" value="{{ old('question', $faq?->question) }}" required placeholder="cth. Berapa minimal pemesanan?" />
    </div>

    <div class="space-y-2">
        <x-label for="answer">Jawaban *</x-label>
        <x-textarea id="answer" name="answer" rows="4" required placeholder="Jawaban singkat dan jelas…">{{ old('answer', $faq?->answer) }}</x-textarea>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="space-y-2">
            <x-label for="position">Urutan Tampil</x-label>
            <x-input id="position" name="position" type="number" min="0" value="{{ old('position', $faq?->position ?? $nextPosition ?? 0) }}" />
        </div>

        <div class="flex items-end pb-2">
            <label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-input accent-[color:var(--color-primary)]" @checked(old('is_active', $faq?->is_active ?? true))>
                Tampilkan di situs
            </label>
        </div>
    </div>
</div>
