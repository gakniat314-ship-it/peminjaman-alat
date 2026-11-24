<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /** Menampilkan daftar pengguna dengan pencarian dan paginasi. */
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('q').'%')
                    ->orWhere('username', 'like', '%'.$request->string('q').'%')
                    ->orWhere('email', 'like', '%'.$request->string('q').'%');
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /** Menampilkan formulir kosong untuk membuat pengguna. */
    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    /** Memvalidasi dan membuat akun pengguna baru. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'petugas', 'peminjam'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);
        LogAktivitas::catat("Menambahkan pengguna #{$user->id} ({$user->username})");

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /** Menampilkan formulir yang berisi data pengguna terpilih. */
    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    /** Memvalidasi dan menyimpan perubahan data akun pengguna. */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'petugas', 'peminjam'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->is(auth()->user()) && $data['role'] !== $user->role) {
            return back()->withInput()->withErrors(['role' => 'Role akun yang sedang digunakan tidak dapat diubah.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        LogAktivitas::catat("Memperbarui pengguna #{$user->id} ({$user->username})");

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /** Menghapus pengguna yang bukan akun aktif dan tidak memiliki transaksi. */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $hasTransactions = Peminjaman::query()
            ->where('user_id', $user->id)
            ->orWhere('petugas_id', $user->id)
            ->exists()
            || Pengembalian::query()->where('petugas_id', $user->id)->exists();

        if ($hasTransactions) {
            return back()->with('error', 'Pengguna memiliki riwayat transaksi dan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($user): void {
            LogAktivitas::catat("Menghapus pengguna #{$user->id} ({$user->username})");
            $user->delete();
        });

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
