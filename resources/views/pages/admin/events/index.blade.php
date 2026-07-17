@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')
    <div class="container mx-auto p-10">
        <!-- Success/Error Alerts -->
        @if(session('error'))
            <div class="alert alert-error mb-4 shadow-sm bg-red-100 border-red-400 text-red-700 p-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 mr-2 text-red-700" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h1 class="text-3xl font-semibold">Manajemen Event</h1>
            <div class="flex items-center gap-2">
                <button type="button" id="bulk_delete_btn" class="btn btn-error text-white hidden" onclick="confirmBulkDelete()">
                    Hapus Terpilih (<span id="selected_count">0</span>)
                </button>
                <a href="{{ route('admin.events.export', request()->all()) }}" class="btn btn-secondary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Tambah Event</a>
            </div>
        </div>

        <!-- Bulk Delete Hidden Form -->
        <form id="bulk_delete_form" action="{{ route('admin.events.bulk-delete') }}" method="POST" class="hidden">
            @csrf
            <div id="bulk_delete_inputs"></div>
        </form>

        <!-- Filter Form -->
        <div class="bg-white p-5 rounded-box shadow-xs mb-6">
            <form method="GET" action="{{ route('admin.events.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Pencarian</span>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau lokasi..." class="input input-bordered w-full" />
                </div>

                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Kategori</span>
                    </label>
                    <select name="kategori_id" class="select select-bordered w-full">
                        <option value="">Semua Kategori</option>
                        @foreach(\App\Models\Kategori::all() as $cat)
                            <option value="{{ $cat->id }}" {{ request('kategori_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Urutkan Tanggal</span>
                    </label>
                    <select name="sort" class="select select-bordered w-full">
                        <option value="asc" {{ request('sort', 'asc') == 'asc' ? 'selected' : '' }}>Terdekat (Asc)</option>
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terjauh (Desc)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">Filter</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table Events -->
        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs mb-6">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" id="select_all_events" class="checkbox" />
                        </th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Tanggal & Waktu</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>
                                <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" class="checkbox event-select-checkbox" data-has-sales="{{ $event->hasSales() ? 'true' : 'false' }}" />
                            </td>
                            <td>
                                <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="w-16 h-16 object-cover rounded-md" />
                            </td>
                            <td class="font-semibold">{{ $event->judul }}</td>
                            <td>{{ $event->kategori->nama ?? '-' }}</td>
                            <td>{{ $event->tanggal_waktu->format('d M Y, H:i') }}</td>
                            <td>{{ $event->lokasis->nama_lokasi ?? '-' }}</td>
                            <td>
                                @if($event->status === 'Upcoming')
                                    <span class="badge badge-info text-white">Upcoming</span>
                                @elseif($event->status === 'Ongoing')
                                    <span class="badge badge-warning text-white">Ongoing</span>
                                @else
                                    <span class="badge badge-success text-white">Completed</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-info text-white" target="_blank">View</a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.events.clone', $event) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Apakah Anda yakin ingin menggandakan event ini?')">Clone</button>
                                    </form>
                                    <button class="btn btn-sm bg-red-500 text-white" onclick="openDeleteModal(this)" data-id="{{ $event->id }}" data-judul="{{ $event->judul }}">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-gray-500">Tidak ada event yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $events->appends(request()->except('page'))->links() }}
        </div>
    </div>

    <!-- Delete Modal -->
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-bold mb-4">Hapus Event</h3>
            <p>Apakah Anda yakin ingin menghapus event <span id="delete_event_judul" class="font-semibold"></span>?</p>
            <p class="text-sm text-gray-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-action">
                <button class="btn btn-error text-white" type="submit">Hapus</button>
                <button class="btn" onclick="delete_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openDeleteModal(button) {
            const id = button.dataset.id;
            const judul = button.dataset.judul;
            const form = document.querySelector('#delete_modal form');
            
            document.getElementById("delete_event_judul").textContent = judul;
            form.action = `{{ url('/admin/events') }}/${id}`;
            
            delete_modal.showModal();
        }

        // Bulk delete logic
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select_all_events');
            const checkboxes = document.querySelectorAll('.event-select-checkbox');
            const bulkDeleteBtn = document.getElementById('bulk_delete_btn');
            const selectedCountSpan = document.getElementById('selected_count');
            const bulkDeleteForm = document.getElementById('bulk_delete_form');
            const bulkDeleteInputs = document.getElementById('bulk_delete_inputs');

            function updateBulkDeleteState() {
                const checkedBoxes = document.querySelectorAll('.event-select-checkbox:checked');
                const count = checkedBoxes.length;
                
                selectedCountSpan.textContent = count;
                if (count > 0) {
                    bulkDeleteBtn.classList.remove('hidden');
                } else {
                    bulkDeleteBtn.classList.add('hidden');
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        cb.checked = selectAllCheckbox.checked;
                    });
                    updateBulkDeleteState();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else {
                        const allChecked = Array.from(checkboxes).every(c => c.checked);
                        selectAllCheckbox.checked = allChecked;
                    }
                    updateBulkDeleteState();
                });
            });

            window.confirmBulkDelete = function() {
                const checkedBoxes = document.querySelectorAll('.event-select-checkbox:checked');
                if (checkedBoxes.length === 0) return;

                let hasSalesCount = 0;
                checkedBoxes.forEach(cb => {
                    if (cb.dataset.hasSales === 'true') {
                        hasSalesCount++;
                    }
                });

                let message = `Apakah Anda yakin ingin menghapus ${checkedBoxes.length} event terpilih?`;
                if (hasSalesCount > 0) {
                    message += `\n\nCatatan: ${hasSalesCount} event memiliki penjualan dan akan dilewati/tidak terhapus.`;
                }

                if (confirm(message)) {
                    bulkDeleteInputs.innerHTML = '';
                    checkedBoxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkDeleteInputs.appendChild(input);
                    });
                    bulkDeleteForm.submit();
                }
            };
        });
    </script>
@endsection
