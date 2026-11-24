{{-- Menyediakan tombol utama untuk mengirim formulir atau menjalankan aksi. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-petrol-800 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-petrol-900/15 hover:bg-petrol-700 focus:bg-petrol-700 active:bg-petrol-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
