<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <span id="realtime-greeting">Selamat ..., {{ Auth::user()->name }}!</span> 🌤️
            </h2>
            <span id="realtime-clock" class="text-sm text-gray-500 font-mono"></span>
        </div>
    </x-slot>

    <div class="py-12 text-slate-800">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6" x-data="{ loading: false }">
                <form action="{{ route('dashboard') }}" method="GET" class="flex gap-4" @submit="loading = true">
                    <input type="text" name="city" placeholder="Cari kota (Contoh: Garut)..." 
                        class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    
                    <button type="submit" :disabled="loading" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Cek Cuaca</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memuat...
                        </span>
                    </button>
                </form>
            </div>

            @if($weather)
            <div class="relative overflow-hidden rounded-2xl shadow-xl max-w-md mx-auto mb-10 text-white bg-cover bg-center min-h-[300px] flex items-center transition-all duration-500"
                 style="background-image: url('{{ $imageUrl ?? 'https://images.unsplash.com/photo-1530908295418-a12e326966ba?q=80&w=1000' }}');">
                
                <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 to-blue-600/30 z-0"></div>
                
                <div class="relative z-10 p-8 w-full">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-3xl font-bold drop-shadow-md">{{ $weather['name'] }}, {{ $weather['sys']['country'] }}</h3>
                            <p class="text-blue-100 italic drop-shadow-sm">{{ ucfirst($weather['weather'][0]['description']) }}</p>
                        </div>
                        <img src="https://openweathermap.org/img/wn/{{ $weather['weather'][0]['icon'] }}@2x.png" alt="icon" class="drop-shadow-md scale-125">
                    </div>
                    <div class="mt-6 text-7xl font-extrabold drop-shadow-lg text-center md:text-left">
                        {{ round($weather['main']['temp']) }}°C
                    </div>
                    
                    <form action="{{ route('weather.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="city_name" value="{{ $weather['name'] }}">
                        <input type="hidden" name="temperature" value="{{ $weather['main']['temp'] }}">
                        <input type="hidden" name="description" value="{{ $weather['weather'][0]['description'] }}">
                        <button type="submit" class="mt-8 w-full bg-white/20 hover:bg-white/40 text-white font-bold py-2 rounded-lg backdrop-blur-md border border-white/30 transition shadow-lg">
                            + Simpan ke Favorit
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm">Total Kota Favorit</p>
                    <p class="text-2xl font-bold">{{ $totalFavorites }} <span class="text-sm font-normal text-gray-400">Kota</span></p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-orange-400">
                    <p class="text-gray-500 text-sm">Rata-rata Suhu Kota Favorit</p>
                    <p class="text-2xl font-bold">{{ number_format($avgTemp, 1) }}°C</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Daftar Kota Favorit</h3>
                
                @if(isset($logs) && $logs->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($logs as $log)
                            <div class="border rounded-xl p-4 flex justify-between items-center bg-gray-50 hover:bg-white hover:shadow-md transition">
                                <div>
                                    <p class="font-bold text-blue-600">{{ $log->city_name }}</p>
                                    <p class="text-2xl font-black">{{ round($log->temperature) }}°C</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ $log->description }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 mb-4">{{ $log->created_at->format('d M, H:i') }}</p>
                                    <form action="{{ route('weather.destroy', $log->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus dari favorit?')" class="text-red-500 text-xs font-bold hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4 text-gray-300">📂</div>
                        <p class="text-gray-500 italic">Belum ada kota favorit yang disimpan.</p>
                        <p class="text-sm text-gray-400">Cari kota di atas dan klik 'Simpan ke Favorit' untuk memulai.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const options = { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('realtime-clock').innerText = now.toLocaleDateString('id-ID', options);

            const hour = now.getHours();
            let greeting = 'Malam';
            if (hour >= 5 && hour < 11) greeting = 'Pagi';
            else if (hour >= 11 && hour < 15) greeting = 'Siang';
            else if (hour >= 15 && hour < 19) greeting = 'Sore';

            document.getElementById('realtime-greeting').innerText = `Selamat ${greeting}, {{ Auth::user()->name }}!`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</x-app-layout>