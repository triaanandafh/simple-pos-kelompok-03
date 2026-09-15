<div>
    <nav class="bg-slate-900 text-white px-4 py-3 flex items-center gap-6">
        <span class="font-semibold">Simple POS</span>
        <a href="{{ route('pos.create') }}" class="hover:underline {{ request()->routeIs('pos.*') ? 'font-bold underline' : '' }}">Kasir</a>
        <a href="{{ route('transactions.index') }}" class="hover:underline {{ request()->routeIs('transactions.*') ? 'font-bold underline' : '' }}">Transaksi</a>
    </nav>
</div>