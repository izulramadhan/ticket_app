<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasis = Lokasi::where('flag_delete',0)->get();

        return view('pages.admin.lokasis', compact('lokasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255|unique:lokasis,nama_lokasi',
        ]);

        Lokasi::create([
            'nama_lokasi' => $request->nama_lokasi,
            'aktif' => $request->aktif,
        ]);

        return redirect()->route('lokasis')
            ->with('success', 'Lokasi berhasil ditambahkan!');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255|unique:lokasis,nama_lokasi,' . $id,
        ]);

        $category = Lokasi::findOrFail($id);
        $category->update([
            'nama_lokasi' => $request->nama_lokasi,
            'aktif' => $request->aktif,
        ]);

        return redirect()->route('lokasis')
            ->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = Lokasi::findOrFail($id);
        $category->update([
            'flag_delete' => 1
        ]);
        
        // $category->delete();

        return redirect()->route('lokasis')
            ->with('success', 'Lokasi berhasil dihapus!');
    }
}
