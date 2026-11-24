{{-- Menyediakan formulir untuk membuat atau memperbarui akun pengguna. --}}
<x-app-layout>
    <x-slot name="header"><p class="text-sm text-indigo-600">Administrator / Pengguna</p><h2 class="text-2xl font-bold text-gray-900">{{ $user->exists ? 'Ubah pengguna' : 'Tambah pengguna' }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <x-flash />
        @if ($errors->any())<div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
            @csrf @if ($user->exists) @method('PUT') @endif
            <div><x-input-label for="name" value="Nama lengkap" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $user->name)" required /></div>
            <div><x-input-label for="username" value="Username" /><x-text-input id="username" name="username" class="mt-1 block w-full" :value="old('username', $user->username)" required /></div>
            <div><x-input-label for="email" value="Email" /><x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required /></div>
            <div><x-input-label for="role" value="Role" /><select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300" required @if ($user->is(auth()->user())) disabled @endif>
                @foreach (['admin' => 'Admin', 'petugas' => 'Petugas', 'peminjam' => 'Peminjam'] as $value => $label)<option value="{{ $value }}" @selected(old('role', $user->role ?: 'peminjam') === $value)>{{ $label }}</option>@endforeach
            </select>@if ($user->is(auth()->user()))<input type="hidden" name="role" value="{{ $user->role }}">@endif</div>
            <div><x-input-label for="password" :value="$user->exists ? 'Password baru (opsional)' : 'Password'" /><x-text-input id="password" name="password" type="password" class="mt-1 block w-full" @required(! $user->exists) autocomplete="new-password" /></div>
            <div><x-input-label for="password_confirmation" value="Konfirmasi password" /><x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" @required(! $user->exists) autocomplete="new-password" /></div>
            <div class="flex gap-3"><x-primary-button>Simpan</x-primary-button><a href="{{ route('admin.users.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div></div>
</x-app-layout>
