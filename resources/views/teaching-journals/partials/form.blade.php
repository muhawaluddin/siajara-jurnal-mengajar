@php($teachingJournal = $teachingJournal ?? null)
@php($selectedClassroomId = old('classroom_id', $teachingJournal->classroom_id ?? ($classrooms->first()?->id ?? '')))
@php($existingPhotoUrl = $teachingJournal?->documentation_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($teachingJournal->documentation_path) : null)

<div class="space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="classroom_id" class="block text-sm font-medium text-slate-600">Kelas</label>
            <select name="classroom_id" id="classroom_id" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Pilih kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((int) $selectedClassroomId === $classroom->id)>
                        {{ $classroom->name }}
                    </option>
                @endforeach
            </select>
            @if($classrooms->isEmpty())
                <p class="mt-2 text-sm text-amber-600">Belum ada data kelas. Silakan tambahkan melalui menu Master Kelas.</p>
            @endif
            @error('classroom_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="subject_id" class="block text-sm font-medium text-slate-600">Mata Pelajaran</label>
            <select name="subject_id" id="subject_id" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Pilih mata pelajaran</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((int) old('subject_id', $teachingJournal->subject_id ?? 0) === $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @if($subjects->isEmpty())
                <p class="mt-2 text-sm text-amber-600">Belum ada mapel yang di-assign ke akun Anda. Minta admin menambahkan mapel di data guru.</p>
            @endif
            @error('subject_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div>
        <label for="tanggal" class="block text-sm font-medium text-slate-600">Tanggal</label>
        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', optional($teachingJournal?->tanggal)->format('Y-m-d')) }}" required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('tanggal')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="jam_mulai" class="block text-sm font-medium text-slate-600">Jam Mulai</label>
            <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', optional($teachingJournal?->jam_mulai)->format('H:i')) }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('jam_mulai')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="jam_selesai" class="block text-sm font-medium text-slate-600">Jam Selesai</label>
            <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', optional($teachingJournal?->jam_selesai)->format('H:i')) }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('jam_selesai')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="topik" class="block text-sm font-medium text-slate-600">Topik Pembelajaran</label>
        <input type="text" name="topik" id="topik" value="{{ old('topik', $teachingJournal->topik ?? '') }}"
               required maxlength="255"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('topik')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="catatan" class="block text-sm font-medium text-slate-600">Catatan</label>
        <textarea name="catatan" id="catatan" rows="4"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200">{{ old('catatan', $teachingJournal->catatan ?? '') }}</textarea>
        @error('catatan')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div x-data="documentationUploader(@js($existingPhotoUrl))" x-init="init()" class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/60 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
            <div class="sm:w-1/2 space-y-3">
                <div>
                    <label for="documentation" class="block text-sm font-medium text-emerald-800">Dokumentasi Kelas</label>
                    <p class="mt-1 text-xs text-emerald-700/80">Ambil foto dari kamera/galeri atau pakai kamera langsung di halaman. Sistem akan mengecilkan dan menyimpan sebagai WebP maksimal 1600px agar hemat storage.</p>
                    <input type="file" name="documentation" id="documentation" accept="image/*" capture="environment"
                           x-ref="input"
                           class="mt-3 block w-full cursor-pointer rounded-lg border border-emerald-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200"
                           @change="preview($event)">
                    @error('documentation')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="rounded-lg border border-emerald-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700/80">Kamera langsung</p>
                        <button type="button" class="text-xs font-semibold text-emerald-700 hover:text-emerald-600 underline" @click="toggleCamera()"
                                x-text="stream ? 'Matikan kamera' : 'Nyalakan kamera'"></button>
                    </div>
                    <template x-if="error">
                        <p class="mt-2 text-xs text-red-600" x-text="error"></p>
                    </template>
                    <div class="mt-2 overflow-hidden rounded-lg border border-emerald-100 bg-slate-900/80">
                        <video x-ref="video" class="aspect-video w-full bg-black" playsinline muted :style="flipHorizontal ? 'transform: scaleX(-1);' : ''"></video>
                        <canvas x-ref="canvas" class="hidden"></canvas>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <button type="button" class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500 disabled:opacity-40" :disabled="!stream" @click="captureFrame()">Ambil Foto</button>
                        <button type="button" class="rounded-lg border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="stopCamera()" :disabled="!stream">Stop</button>
                        <button type="button" class="rounded-lg border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="flipHorizontal = !flipHorizontal">Balik orientasi</button>
                    </div>
                </div>
            </div>
            <div class="sm:w-1/2">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700/80 mb-2">Pratinjau</p>
                <template x-if="previewUrl">
                    <img :src="previewUrl" alt="Pratinjau dokumentasi" class="aspect-video w-full rounded-lg border border-emerald-200 object-cover shadow-sm">
                </template>
                <p class="text-xs text-emerald-700/70" x-show="!previewUrl">Belum ada foto. Ambil gambar untuk dokumentasi kegiatan.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('web.teaching-journals.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>

<script>
    function documentationUploader(initialUrl = '') {
        return {
            previewUrl: initialUrl,
            stream: null,
            error: '',
            flipHorizontal: true,
            init() {
                // Clean up stream when user leaves the page.
                window.addEventListener('beforeunload', () => this.stopCamera());
            },
            async toggleCamera() {
                if (this.stream) {
                    this.stopCamera();
                    return;
                }
                this.error = '';
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    this.$refs.video.srcObject = this.stream;
                    await this.$refs.video.play();
                } catch (e) {
                    this.error = 'Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan.';
                    this.stopCamera();
                }
            },
            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }
                this.stream = null;
                if (this.$refs.video) {
                    this.$refs.video.pause();
                    this.$refs.video.srcObject = null;
                }
            },
            captureFrame() {
                if (!this.stream || !this.$refs.video) return;
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.save();
                if (this.flipHorizontal) {
                    ctx.translate(width, 0);
                    ctx.scale(-1, 1);
                }
                ctx.drawImage(video, 0, 0, width, height);
                ctx.restore();
                canvas.toBlob((blob) => {
                    if (!blob) return;
                    const file = new File([blob], 'kamera.webp', { type: blob.type || 'image/webp' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.input.files = dt.files;
                    this.previewUrl = URL.createObjectURL(blob);
                }, 'image/webp', 0.8);
            },
            preview(event) {
                const [file] = event.target.files || [];
                this.previewUrl = file ? URL.createObjectURL(file) : initialUrl;
            },
        };
    }
</script>
