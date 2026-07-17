@extends('layouts.admin_layouts')

@section('title', 'Manajemen Lokasi')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex">
            <h1 class="text-3xl font-semibold mb-4">Manajemen Lokasi</h1>
            <button class="btn btn-primary ml-auto" onclick="add_modal.showModal()">Tambah Lokasi</button>
        </div>
        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
            <table class="table">
                <!-- head -->
                <thead>
                    <tr>
                        <th>No</th>
                        <th class="w-3/4">Nama Lokasi</th>
                        <th class="w-3/4">Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lokasis as $index => $category)
                    <tr>
                        <th>{{ $index + 1 }}</th>
                        <td>{{ $category->nama_lokasi }}</td>
                        <td>
                                @if($category->aktif === 'Y')
                                <span class="badge badge-success text-white">Aktif</span>
                                @else
                                    <span class="badge badge-warning text-white">Tidak Aktif</span>

                                @endif
                            </td>
                        <td>
                            <button class="btn btn-sm btn-primary mr-2" onclick="openEditModal(this)" data-id="{{ $category->id }}" data-nama="{{ $category->nama_lokasi }}" data-aktif="{{ $category->nama_aktif }}">Edit</button>
                            <button class="btn btn-sm bg-red-500 text-white" onclick="openDeleteModal(this)" data-id="{{ $category->id }}">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada kategori tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Modal -->
    <dialog id="add_modal" class="modal">
        <form method="POST" action="{{ route('lokasis.store') }}" class="modal-box">
            @csrf
            <h3 class="text-lg font-bold mb-4">Tambah Lokasi</h3>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Nama Lokasi</span>
                </label>
                <input type="text" placeholder="Masukkan nama lokasi" class="input input-bordered w-full" name="nama_lokasi" required />
            </div>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Status</span>
                </label>
                <select name="aktif" required class="select select-bordered w-full @error('aktif') select-error @enderror">
                                <option value="Y" selected>Aktif</option>
                                <option value="N">Tidak Aktif</option>
                            </select>            
            </div>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="add_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Edit Category Modal With Retrieve ID -->
     <dialog id="edit_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('PUT')

            <input type="hidden" name="lokasis_id" id="edit_lokasis_id">

            <h3 class="text-lg font-bold mb-4">Edit Lokasi</h3>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Nama Lokasi</span>
                </label>
                <input type="text" placeholder="Masukkan nama kategori" class="input input-bordered w-full" value="Lokasi Contoh" id="edit_lokasis_name" name="nama_lokasi" />
            </div>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Status</span>
                </label>
                <select id="edit_lokasis_aktif" name="aktif" required class="select select-bordered w-full @error('aktif') select-error @enderror">
                    <option value="Y" selected>Aktif</option>
                    <option value="N">Tidak Aktif</option>
                </select>
            </div>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="edit_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Delete Modal -->
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <input type="hidden" name="lokasis_id" id="delete_lokasis_id">

            <h3 class="text-lg font-bold mb-4">Hapus Lokasi</h3>
            <p>Apakah Anda yakin ingin menghapus kategori ini?</p>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Hapus</button>
                <button class="btn" onclick="delete_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openEditModal(button) {
            console.log(button.dataset.nama, 'ccc');
            const name = button.dataset.nama;
            const id = button.dataset.id;
            const aktif = button.dataset.aktif;
            const form = document.querySelector('#edit_modal form');

            document.getElementById("edit_lokasis_name").value = name;
            document.getElementById("edit_lokasis_id").value = id;
            document.getElementById("edit_lokasis_aktif").value = aktif;

             // Set action dengan parameter ID
            form.action = `{{ url('/admin/lokasis') }}/${id}`

            edit_modal.showModal();
        }

        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.querySelector('#delete_modal form');
            document.getElementById("delete_lokasis_id").value = id;

            // Set action dengan parameter ID
            form.action = `{{ url('/admin/lokasis') }}/${id}`

            delete_modal.showModal();
        }
</script>


@endsection
