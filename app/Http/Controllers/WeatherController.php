<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function index(Request $request)
    {
        $weather = null;
        $imageUrl = null; // Menampung URL gambar dari Unsplash
        $city = $request->input('city');

        // 1. Ambil data favorit user 
        $logs = WeatherLog::where('user_id', auth()->id())->latest()->get();

        // 2. Hitung Statistik untuk Panel Dashboard
        $totalFavorites = $logs->count();
        $avgTemp = $logs->avg('temperature');

        // 3. Logika Pencarian API Cuaca & Unsplash
        if ($city) {
            $apiKey = env('OPENWEATHER_API_KEY');
            $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'id'
            ]);

            if ($response->successful()) {
                $weather = $response->json();

                // Panggil API Unsplash untuk mencari foto kota secara dinamis
                try {
                    $unsplashKey = env('UNSPLASH_ACCESS_KEY');
                    $photoResponse = Http::get("https://api.unsplash.com/search/photos", [
                        'query' => $weather['name'] . ' city landmark',
                        'per_page' => 1,
                        'orientation' => 'landscape',
                        'client_id' => $unsplashKey
                    ]);

                    if ($photoResponse->successful() && count($photoResponse->json()['results']) > 0) {
                        $imageUrl = $photoResponse->json()['results'][0]['urls']['regular'];
                    }
                } catch (\Exception $e) {
                    // Jika Unsplash error, imageUrl akan tetap null
                }
            } else {
                return back()->with('error', 'Kota tidak ditemukan.');
            }
        }

        // 4. Kirim semua data ke tampilan
        return view('dashboard', [
            'weather' => $weather,
            'logs' => $logs,
            'totalFavorites' => $totalFavorites,
            'avgTemp' => $avgTemp,
            'imageUrl' => $imageUrl
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'city_name' => 'required',
            'temperature' => 'required',
            'description' => 'required',
        ]);

        WeatherLog::create([
            'user_id' => auth()->id(),
            'city_name' => $request->city_name,
            'temperature' => $request->temperature,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Kota berhasil disimpan ke favorit!');
    }

    public function destroy($id)
    {
        $log = WeatherLog::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $log->delete();

        return back()->with('success', 'Kota berhasil dihapus dari favorit.');
    }
}