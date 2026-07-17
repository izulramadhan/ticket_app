@extends('layouts.admin_layouts')

@section('title', 'Tambah Event')

@section('content')
    <!-- Cropper.js CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

    <div class="container mx-auto p-10 max-w-4xl">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-error mb-6 shadow-sm bg-red-100 border-red-400 text-red-700 p-4 rounded-lg">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Form -->
        <div class="card bg-white shadow-md rounded-box p-8">
            <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Event Details Section -->
                <div>
                    <h2 class="text-xl font-bold mb-6 pb-2 border-b">Detail Event</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Judul Event -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Judul Event</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="text" name="judul" value="{{ old('judul') }}" required placeholder="Masukkan judul event" class="input input-bordered w-full @error('judul') input-error @enderror" />
                        </div>

                        <!-- Kategori Dropdown -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Kategori</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <select name="kategori_id" required class="select select-bordered w-full @error('kategori_id') select-error @enderror">
                                <option value="" disabled selected>Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('kategori_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lokasi -->
                        <!-- <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Lokasi</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="text" name="lokasi" value="{{ old('lokasi') }}" required placeholder="Masukkan lokasi event" class="input input-bordered w-full @error('lokasi') input-error @enderror" />
                        </div> -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Lokasi</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <select name="lokasi" required class="select select-bordered w-full @error('lokasi') select-error @enderror">
                                <option value="" disabled selected>Pilih Lokasi</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('lokasi') == $location->id ? 'selected' : '' }}>
                                        {{ $location->nama_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <!-- Tanggal & Waktu -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu') }}" required class="input input-bordered w-full @error('tanggal_waktu') input-error @enderror" />
                        </div>

                        <!-- Gambar File Input -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Gambar Event</span>
                                <span class="text-xs text-gray-500 block mb-1">Maksimal 2MB, format JPG/JPEG/PNG</span>
                            </label>
                             <input type="file" name="gambar" id="gambar_input" accept="image/png, image/jpeg, image/jpg" class="file-input file-input-bordered w-full @error('gambar') file-input-error @enderror" />
                             <input type="hidden" name="gambar_cropped" id="gambar_cropped" />
                             
                             <!-- Image Preview Container -->
                             <div id="image_preview_container" class="hidden mt-3 p-2 border rounded-md w-max bg-gray-50">
                                 <p class="text-xs text-gray-500 mb-2">Pratinjau Gambar:</p>
                                 <img id="image_preview" src="#" alt="Pratinjau" class="max-h-48 object-contain rounded-md" />
                             </div>

                             <!-- Image Cropping Modal -->
                             <dialog id="cropper_modal" class="modal">
                                 <div class="modal-box max-w-2xl bg-white p-6 rounded-lg">
                                     <h3 class="text-lg font-bold mb-4 text-gray-800">Potong Gambar Event</h3>
                                     <div class="max-h-96 overflow-hidden flex justify-center bg-gray-100 rounded-md">
                                         <img id="cropper_source" src="" class="max-w-full max-h-96 object-contain" />
                                     </div>
                                     <div class="modal-action flex justify-end gap-2 mt-4">
                                         <button type="button" class="btn btn-primary" id="crop_save_btn">Potong & Simpan</button>
                                         <button type="button" class="btn" onclick="cancelCropping()">Batal</button>
                                     </div>
                                 </div>
                             </dialog>
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Deskripsi Event</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <textarea name="deskripsi" rows="5" required placeholder="Masukkan deskripsi lengkap event..." class="textarea textarea-bordered w-full @error('deskripsi') textarea-error @enderror">{{ old('deskripsi') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Ticket Form Section -->
                <div>
                    <div class="flex items-center justify-between mb-6 pb-2 border-b">
                        <h2 class="text-xl font-bold">Informasi Tiket</h2>
                        <button type="button" id="add_ticket_btn" class="btn btn-sm btn-secondary gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Tiket
                        </button>
                    </div>

                    <!-- Ticket Container -->
                    <div id="tickets_container" class="space-y-4">
                        <!-- Dynamic card template inserted here -->
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t flex justify-end">
                    <button type="submit" class="btn btn-primary px-8">Simpan Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Preview and Dynamic Tickets -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 2. Dynamic Ticket Forms logic
            const container = document.getElementById('tickets_container');
            const addBtn = document.getElementById('add_ticket_btn');
            let ticketIndex = 0;

            function renderTicketCard(index) {
                const card = document.createElement('div');
                card.className = 'ticket-card card bg-gray-50 border p-5 rounded-lg relative space-y-4';
                card.dataset.index = index;

                card.innerHTML = `
                    <div class="flex items-center justify-between border-b pb-2">
                        <span class="font-bold text-sm text-gray-700">Tiket #${index + 1}</span>
                        <button type="button" class="btn btn-xs btn-error text-white remove-ticket-btn" onclick="removeTicket(${index})">
                            Hapus
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Tipe Tiket -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Tipe Tiket</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <select name="tikets[${index}][tipe]" required class="select select-bordered w-full select-sm">
                                <option value="reguler">Reguler</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>

                        <!-- Harga -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Harga</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="number" name="tikets[${index}][harga]" min="0" required class="input input-bordered input-sm w-full" placeholder="0" />
                        </div>

                        <!-- Stok -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Stok</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="number" name="tikets[${index}][stok]" min="0" required class="input input-bordered input-sm w-full" placeholder="0" />
                        </div>
                    </div>
                `;
                return card;
            }

            // Cropper.js & Image Preview logic
            const gambarInput = document.getElementById('gambar_input');
            const previewContainer = document.getElementById('image_preview_container');
            const previewImage = document.getElementById('image_preview');
            const gambarCroppedInput = document.getElementById('gambar_cropped');
            const cropperModal = document.getElementById('cropper_modal');
            const cropperSource = document.getElementById('cropper_source');
            const cropSaveBtn = document.getElementById('crop_save_btn');
            let cropper = null;

            gambarInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        cropperSource.src = e.target.result;
                        cropperModal.showModal();
                        
                        if (cropper) {
                            cropper.destroy();
                        }
                        
                        cropper = new Cropper(cropperSource, {
                            aspectRatio: 4 / 3,
                            viewMode: 1,
                            background: false,
                        });
                    }
                    reader.readAsDataURL(file);
                }
            });

            cropSaveBtn.addEventListener('click', function () {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({
                        width: 800,
                        height: 600,
                    });
                    
                    const croppedBase64 = canvas.toDataURL('image/jpeg');
                    gambarCroppedInput.value = croppedBase64;
                    previewImage.src = croppedBase64;
                    previewContainer.classList.remove('hidden');
                    
                    cropperModal.close();
                }
            });

            window.cancelCropping = function() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                gambarInput.value = '';
                cropperModal.close();
            };

            // Function to add a ticket
            function addTicket() {
                const card = renderTicketCard(ticketIndex);
                container.appendChild(card);
                ticketIndex++;
                updateTicketHeaders();
            }

            // Global removal function
            window.removeTicket = function (index) {
                const card = container.querySelector(`[data-index="${index}"]`);
                if (card) {
                    card.remove();
                    updateTicketHeaders();
                }
            };

            // Maintain correct numbering headers for tickets
            function updateTicketHeaders() {
                const cards = container.querySelectorAll('.ticket-card');
                cards.forEach((card, idx) => {
                    const headerText = card.querySelector('.flex span');
                    if (headerText) {
                        headerText.textContent = `Tiket #${idx + 1}`;
                    }
                });
            }

            // Add ticket button listener
            addBtn.addEventListener('click', addTicket);

            // Add 1 ticket by default
            addTicket();
        });
    </script>
@endsection
