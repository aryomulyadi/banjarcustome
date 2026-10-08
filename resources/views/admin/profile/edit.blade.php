<x-layouts.admin :title="'Profil Admin'">

    <div class="max-w-xl space-y-6">
        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Akun</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">Nama</dt>
                    <dd class="font-medium">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">Email</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">Role</dt>
                    <dd class="font-medium uppercase">{{ $user->role }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">Login Terakhir</dt>
                    <dd class="font-medium">{{ $user->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah login' }}</dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="rounded-xl border border-border bg-card p-5">
            @csrf
            @method('PUT')

            <h2 class="text-base font-semibold">Ganti Password</h2>

            <div class="mt-4 space-y-4">
                <div class="space-y-2">
                    <x-label for="current_password">Password Saat Ini *</x-label>
                    <x-input id="current_password" name="current_password" type="password" required autocomplete="current-password" />
                    @error('current_password') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="password">Password Baru *</x-label>
                    <x-input id="password" name="password" type="password" required autocomplete="new-password" />
                    @error('password') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="password_confirmation">Ulangi Password Baru *</x-label>
                    <x-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                </div>

                <p class="text-xs text-muted-foreground">Minimal 8 karakter.</p>
            </div>

            <div class="mt-5">
                <x-button type="submit">Simpan Password</x-button>
            </div>
        </form>
    </div>

</x-layouts.admin>
