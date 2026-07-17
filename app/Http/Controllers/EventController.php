<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use App\Http\Requests\EventFormRequest;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        $query = Event::with(['kategori', 'tikets','lokasis']);

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

        $events = $query->paginate(10);

        return view('pages.admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $categories = Kategori::all();
        $locations = Lokasi::all();

        return view('pages.admin.events.create', compact('categories','locations'));
    }

    public function store(EventFormRequest $request)
    {
        $gambarPath = 'konser.jpg';
        if ($request->filled('gambar_cropped')) {
            $base64Image = $request->gambar_cropped;
            $image_parts = explode(";base64,", $base64Image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1] ?? 'png';
            $image_base64 = base64_decode($image_parts[1]);
            
            $filename = 'events/' . uniqid() . '.' . $image_type;
            Storage::disk('public')->put($filename, $image_base64);
            $gambarPath = $filename;
        } elseif ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('events', 'public');
        }

        $event = Event::create([
            'user_id' => auth()->id(),
            'kategori_id' => $request->kategori_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'lokasi' => $request->lokasi,
            'tanggal_waktu' => $request->tanggal_waktu,
            'gambar' => $gambarPath,
        ]);

        if ($request->has('tikets')) {
            foreach ($request->tikets as $tiketData) {
                $event->tikets()->create([
                    'tipe' => $tiketData['tipe'],
                    'harga' => $tiketData['harga'],
                    'stok' => $tiketData['stok'],
                ]);
            }
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $categories = Kategori::all();
        $locations = Lokasi::all();
        $event->load('tikets');
        $hasSales = $event->hasSales();

        $ticketsData = $event->tikets->map(function($t) {
            return [
                'id' => $t->id,
                'tipe' => $t->tipe,
                'harga' => $t->harga,
                'stok' => $t->stok,
                'has_sales' => $t->orders()->exists()
            ];
        });

        return view('pages.admin.events.edit', compact('event', 'categories', 'hasSales', 'ticketsData', 'locations'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(EventFormRequest $request, Event $event)
    {
        if ($event->hasSales()) {
            $oldTime = $event->tanggal_waktu->toDateTimeString();
            $newTime = \Carbon\Carbon::parse($request->tanggal_waktu)->toDateTimeString();

            if ($oldTime !== $newTime) {
                return back()->withErrors(['tanggal_waktu' => 'Tanggal dan waktu event tidak boleh diubah karena tiket sudah terjual.'])->withInput();
            }
        }

        $data = [
            'kategori_id' => $request->kategori_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'lokasi' => $request->lokasi,
            'tanggal_waktu' => $request->tanggal_waktu,
        ];

        if ($request->filled('gambar_cropped')) {
            if ($event->gambar && $event->gambar !== 'konser.jpg' && !filter_var($event->gambar, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($event->gambar);
            }
            $base64Image = $request->gambar_cropped;
            $image_parts = explode(";base64,", $base64Image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1] ?? 'png';
            $image_base64 = base64_decode($image_parts[1]);
            
            $filename = 'events/' . uniqid() . '.' . $image_type;
            Storage::disk('public')->put($filename, $image_base64);
            $data['gambar'] = $filename;
        } elseif ($request->hasFile('gambar')) {
            if ($event->gambar && $event->gambar !== 'konser.jpg' && !filter_var($event->gambar, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($event->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('events', 'public');
        }

        $event->update($data);

        $submittedTicketIds = [];

        if ($request->has('tikets')) {
            foreach ($request->tikets as $tiketData) {
                if (!empty($tiketData['id'])) {
                    $tiket = $event->tikets()->findOrFail($tiketData['id']);
                    $tiket->update([
                        'tipe' => $tiketData['tipe'],
                        'harga' => $tiketData['harga'],
                        'stok' => $tiketData['stok'],
                    ]);
                    $submittedTicketIds[] = $tiket->id;
                } else {
                    $newTiket = $event->tikets()->create([
                        'tipe' => $tiketData['tipe'],
                        'harga' => $tiketData['harga'],
                        'stok' => $tiketData['stok'],
                    ]);
                    $submittedTicketIds[] = $newTiket->id;
                }
            }
        }

        if (!$event->hasSales()) {
            $event->tikets()->whereNotIn('id', $submittedTicketIds)->delete();
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diperbarui!');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->hasSales()) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Event tidak dapat dihapus karena sudah memiliki penjualan!');
        }

        if ($event->gambar && $event->gambar !== 'konser.jpg') {
            Storage::disk('public')->delete($event->gambar);
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        // Load the event with its relationships
        $event->load(['kategori', 'tikets']);

        // Fetch related events with same category, upcoming, max 4 events, excluding current event
        $relatedEvents = Event::with('tikets')
            ->where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->upcoming()
            ->take(4)
            ->get();

        return view('events.show', [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
    }

    /**
     * Export events to CSV.
     */
    public function export(Request $request)
    {
        $query = Event::with(['kategori', 'tikets']);

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

        $events = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="events_export_' . now()->format('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($events) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, ['ID', 'Judul Event', 'Kategori', 'Tanggal & Waktu', 'Lokasi', 'Status', 'Tiket & Stok']);
            
            foreach ($events as $event) {
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
    public function clone(Event $event)
    {
        $newEvent = $event->replicate();
        $newEvent->judul = 'Clone of ' . $event->judul;

        if ($event->gambar && $event->gambar !== 'konser.jpg' && !filter_var($event->gambar, FILTER_VALIDATE_URL)) {
            $oldPath = $event->gambar;
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
            $newPath = 'events/' . uniqid() . '.' . $extension;
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->copy($oldPath, $newPath);
                $newEvent->gambar = $newPath;
            }
        }

        $newEvent->save();

        foreach ($event->tikets as $tiket) {
            $newTiket = $tiket->replicate();
            $newTiket->event_id = $newEvent->id;
            $newTiket->save();
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diclone!');
    }

    /**
     * Bulk delete events.
     */
    public function bulkDestroy(Request $request)
    {
        if (!$request->has('ids') || empty($request->ids)) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak ada event yang dipilih.');
        }

        $events = Event::whereIn('id', $request->ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($events as $event) {
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
            return redirect()->route('admin.events.index')
                ->with('success', "$deletedCount event berhasil dihapus, $skippedCount event dilewati karena sudah memiliki penjualan.");
        }

        return redirect()->route('admin.events.index')
            ->with('success', "$deletedCount event berhasil dihapus.");
    }
}
