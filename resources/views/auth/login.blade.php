<x-layouts.app :title="'Login Admin — Banjar Custome'" :noindex="true">

    <div class="mx-auto flex max-w-md flex-col justify-center px-4 py-14 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8">
            <div class="text-center">
                <x-logo size="lg" class="mx-auto" />
                <h1 class="mt-4 text-2xl font-bold">Login Admin</h1>
                <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">
                    Masuk untuk mengelola pesanan dan katalog Banjar Custome.
                </p>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div class="space-y-2">
                    <x-label for="email">Email</x-label>
                    <x-input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@banjarcustom.test"
                    />
                </div>

                <div class="space-y-2">
                    <x-label for="password">Password</x-label>
                    <x-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-input accent-primary"
                    />
                    <span class="text-muted-foreground">Ingat saya</span>
                </label>

                <x-button type="submit" class="w-full">Masuk</x-button>
            </form>
        </div>
    </div>

</x-layouts.app>
