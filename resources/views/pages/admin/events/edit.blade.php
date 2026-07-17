@extends('layouts.admin_layouts')

@section('title', 'Edit Event')

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

        <!-- Warning Alert if has sales -->
        @if($hasSales)
            <div class="alert alert-warning mb-6 shadow-sm bg-yellow-50 border-yellow-400 text-yellow-800 p-4 rounded-lg flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 mr-3 text-yellow-600 mt-0.5" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <span class="font-bold block">Peringatan:</span>
                    <span>Event ini sudah memiliki penjualan tiket. Beberapa field mungkin tidak dapat diubah.</span>
                </div>
            </div>
        @endif

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
            <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

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
                            <input type="text" name="judul" value="{{ old('judul', $event->judul) }}" required placeholder="Masukkan judul event" class="input input-bordered w-full @error('judul') input-error @enderror" />
                        </div>

                        <!-- Kategori Dropdown -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Kategori</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <select name="kategori_id" required class="select select-bordered w-full @error('kategori_id') select-error @enderror">
                                <option value="" disabled>Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('kategori_id', $event->kategori_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lokasi -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Lokasi</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="text" name="lokasi" value="{{ old('lokasi', $event->lokasi) }}" required placeholder="Masukkan lokasi event" class="input input-bordered w-full @error('lokasi') input-error @enderror" />
                        </div>

                        <!-- Tanggal & Waktu -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                                @if($hasSales)
                                    <span class="text-error text-red-500">* (Readonly - tiket sudah terjual)</span>
                                @else
                                    <span class="text-error text-red-500">*</span>
                                @endif
                            </label>
                            <input type="datetime-local" name="tanggal_waktu" 
                                   value="{{ old('tanggal_waktu', $event->tanggal_waktu ? $event->tanggal_waktu->format('Y-m-d\TH:i') : '') }}" 
                                   required 
                                   class="input input-bordered w-full @error('tanggal_waktu') input-error @enderror" 
                                   {{ $hasSales ? 'readonly' : '' }} />
                        </div>

                        <!-- Gambar File Input -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Gambar Event</span>
                                <span class="text-xs text-gray-500 block mb-1">Maksimal 2MB, format JPG/JPEG/PNG. Kosongkan jika tidak ingin mengubah gambar.</span>
                            </label>
                            <input type="file" name="gambar" id="gambar_input" accept="image/png, image/jpeg, image/jpg" class="file-input file-input-bordered w-full @error('gambar') file-input-error @enderror" />
                            <input type="hidden" name="gambar_cropped" id="gambar_cropped" />
                            
                            <!-- Current Image Preview -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div class="p-2 border rounded-md bg-gray-50">
                                    <p class="text-xs text-gray-500 mb-2">Gambar Saat Ini:</p>
                                    <img src="{{ $event->image_url }}" alt="Current Image" class="max-h-48 object-contain rounded-md" />
                                </div>

                                <!-- New Image Preview Container -->
                                <div id="image_preview_container" class="hidden p-2 border rounded-md bg-gray-50">
                                    <p class="text-xs text-gray-500 mb-2">Pratinjau Gambar Baru:</p>
                                    <img id="image_preview" src="#" alt="Pratinjau Baru" class="max-h-48 object-contain rounded-md" />
                                </div>
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
                            <textarea name="deskripsi" rows="5" required placeholder="Masukkan deskripsi lengkap event..." class="textarea textarea-bordered w-full @error('deskripsi') textarea-error @enderror">{{ old('deskripsi', $event->deskripsi) }}</textarea>
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
                    <button type="submit" class="btn btn-primary px-8">Perbarui Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Preview and Dynamic Tickets -->
    <script>
        const existingTickets = @json($ticketsData);

        document.addEventListener('DOMContentLoaded', function () {
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

            // 2. Dynamic Ticket Forms logic
            const container = document.getElementById('tickets_container');
            const addBtn = document.getElementById('add_ticket_btn');
            let ticketIndex = 0;

            function renderTicketCard(index, ticketData = null) {
                const card = document.createElement('div');
                card.className = 'ticket-card card bg-gray-50 border p-5 rounded-lg relative space-y-4';
                card.dataset.index = index;

                const hasSales = ticketData ? ticketData.has_sales : false;
                const ticketId = ticketData ? ticketData.id : '';
                const tipe = ticketData ? ticketData.tipe : 'reguler';
                const harga = ticketData ? ticketData.harga : '';
                const stok = ticketData ? ticketData.stok : '';

                card.innerHTML = `
                    <input type="hidden" name="tikets[${index}][id]" value="${ticketId}" />
                    <div class="flex items-center justify-between border-b pb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-gray-700">Tiket #${index + 1}</span>
                            ${hasSales ? '<span class="badge badge-warning text-white font-semibold text-xs">Sudah Terjual</span>' : ''}
                        </div>
                        ${!hasSales ? `
                            <button type="button" class="btn btn-xs btn-error text-white remove-ticket-btn" onclick="removeTicket(${index})">
                                Hapus
                            </button>
                        ` : ''}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Tipe Tiket -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Tipe Tiket</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <select name="tikets[${index}][tipe]" required class="select select-bordered w-full select-sm" ${hasSales ? 'disabled' : ''}>
                                <option value="reguler" ${tipe === 'reguler' ? 'selected' : ''}>Reguler</option>
                                <option value="premium" ${tipe === 'premium' ? 'selected' : ''}>Premium</option>
                            </select>
                            ${hasSales ? `<input type="hidden" name="tikets[${index}][tipe]" value="${tipe}" />` : ''}
                        </div>

                        <!-- Harga -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Harga</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="number" name="tikets[${index}][harga]" min="0" value="${harga}" required class="input input-bordered input-sm w-full" placeholder="0" ${hasSales ? 'readonly' : ''} />
                        </div>

                        <!-- Stok -->
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Stok</span>
                                <span class="text-error text-red-500">*</span>
                            </label>
                            <input type="number" name="tikets[${index}][stok]" min="0" value="${stok}" required class="input input-bordered input-sm w-full" placeholder="0" />
                        </div>
                    </div>
                `;
                return card;
            }

            // Function to add a ticket
            function addTicket(ticketData = null) {
                const card = renderTicketCard(ticketIndex, ticketData);
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
            addBtn.addEventListener('click', () => addTicket());

            // Pre-populate with existing tickets if available
            if (existingTickets && existingTickets.length > 0) {
                existingTickets.forEach(ticket => {
                    addTicket(ticket);
                });
            } else {
                addTicket();
            }
        });
    </script>
@endsection
