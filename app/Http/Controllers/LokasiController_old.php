<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use App\Models\Kategori;
use App\Http\Requests\LokasiFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LokasiController extends Controller
{
    /**
     * Display a listing of the lokasis.
     */
    public function index(Request $request)
    {
        $query = Lokasi::where('flag_delete',0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lokasi', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'asc');
        $query->orderBy('id', $sort);

        $lokasis = $query->paginate(10);

        return view('pages.admin.lokasis.index', compact('lokasis'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        return view('pages.admin.lokasis.create');
    }

    public function store(LokasiFormRequest $request)
    {

        $lokasi = Lokasi::create([
            'nama_lokasi' => $request->nama_lokasi,
            'aktif' => $request->aktif,
        ]);

        return redirect()->route('admin.lokasis.index')
            ->with('success', 'Lokasi berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Lokasi $event)
    {
        return view('pages.admin.lokasis.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(LokasiFormRequest $request, Lokasi $event)
    {
        $data = [
            'nama_lokasi' => $request->nama_lokasi,
            'aktif' => $request->aktif,
        ];

        $event->update($data);

        return redirect()->route('admin.lokasis.index')
            ->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Lokasi $event)
    {

        // $event->delete();

        Lokasi::where('id',$event->id)
        ->update([
            'flag_delete' => 1
        ]);
        return redirect()->route('admin.lokasis.index')
            ->with('success', 'Lokasi berhasil dihapus!');
    }

    /**
     * Display the specified event.
     */
    public function show(Lokasi $event)
    {
        // Load the event with its relationships
        $event->load(['kategori', 'tikets']);

        // Fetch related lokasis with same category, upcoming, max 4 lokasis, excluding current event
        $relatedLokasis = Lokasi::with('tikets')
            ->where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->upcoming()
            ->take(4)
            ->get();

        return view('lokasis.show', [
            'event' => $event,
            'relatedLokasis' => $relatedLokasis,
        ]);
    }

    /**
     * Export lokasis to CSV.
     */
    public function export(Request $request)
    {
        $query = Lokasi::with(['kategori', 'tikets']);

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'asc');
        $query->orderBy('tanggal_waktu', $sort);

        $lokasis = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="lokasis_export_' . now()->format('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($lokasis) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, ['ID', 'Judul Lokasi', 'Kategori', 'Tanggal & Waktu', 'Lokasi', 'Status', 'Tiket & Stok']);
            
            foreach ($lokasis as $event) {
                $ticketsInfo = $event->tikets->map(function($t) {
                    return ucfirst($t->tipe) . ': Rp' . number_format($t->harga, 0, ',', '.') . ' (Stok: ' . $t->stok . ')';
                })->implode(' | ');
                
                fputcsv($file, [
                    $event->id,
                    $event->judul,
                    $event->kategori?->nama ?? '-',
                    $event->tanggal_waktu ? $event->tanggal_waktu->format('Y-m-d H:i') : '-',
                    $event->lokasi,
                    $event->status,
                    $ticketsInfo
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clone/Duplicate an event.
     */
    public function clone(Lokasi $event)
    {
        $newLokasi = $event->replicate();
        $newLokasi->judul = 'Clone of ' . $event->judul;

        if ($event->gambar && $event->gambar !== 'konser.jpg' && !filter_var($event->gambar, FILTER_VALIDATE_URL)) {
            $oldPath = $event->gambar;
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
            $newPath = 'lokasis/' . uniqid() . '.' . $extension;
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->copy($oldPath, $newPath);
                $newLokasi->gambar = $newPath;
            }
        }

        $newLokasi->save();

        foreach ($event->tikets as $tiket) {
            $newTiket = $tiket->replicate();
            $newTiket->event_id = $newLokasi->id;
            $newTiket->save();
        }

        return redirect()->route('admin.lokasis.index')
            ->with('success', 'Lokasi berhasil diclone!');
    }

    /**
     * Bulk delete lokasis.
     */
    public function bulkDestroy(Request $request)
    {
        if (!$request->has('ids') || empty($request->ids)) {
            return redirect()->route('admin.lokasis.index')
                ->with('error', 'Tidak ada event yang dipilih.');
        }

        $lokasis = Lokasi::whereIn('id', $request->ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($lokasis as $event) {
            if ($event->hasSales()) {
                $skippedCount++;
                continue;
            }

            if ($event->gambar && $event->gambar !== 'konser.jpg' && !filter_var($event->gambar, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($event->gambar);
            }

            $event->delete();
            $deletedCount++;
        }

        if ($skippedCount > 0) {
            return redirect()->route('admin.lokasis.index')
                ->with('success', "$deletedCount event berhasil dihapus, $skippedCount event dilewati karena sudah memiliki penjualan.");
        }

        return redirect()->route('admin.lokasis.index')
            ->with('success', "$deletedCount event berhasil dihapus.");
    }
}
