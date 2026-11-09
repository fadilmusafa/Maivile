@extends('layouts.app')

@section('title', 'Pengambilan Ijazah')

@section('content')
@php $represented = (bool) old('represented'); @endphp
<div class="row">
    <div class="col-xl-8">
        <div class="card overflow-hidden" style="border-top: 4px solid var(--mv-orange)">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-mortarboard-fill fs-4" style="color: var(--mv-orange)" aria-hidden="true"></i>
                    <h2 class="h5 mb-0">Ajukan Jadwal Pengambilan Ijazah</h2>
                </div>
                <p class="text-secondary">Pilih tanggal pada hari kerja. Jadwal dikonfirmasi petugas, dan ijazah baru dianggap diserahkan setelah serah terima dicatat. Bawa kartu identitas asli saat mengambil.</p>

                <form method="POST" action="{{ route('student.diploma.store') }}" enctype="multipart/form-data" data-loading novalidate>
                    @csrf

                    <div class="alert alert-secondary py-2">
                        <i class="bi bi-person-vcard me-1" aria-hidden="true"></i>
                        {{ auth()->user()->name }} &middot; NIS {{ auth()->user()->nis ?? '-' }}
                        @if (auth()->user()->class_name) &middot; Kelas {{ auth()->user()->class_name }} @endif
                    </div>

                    @if ($documents->isNotEmpty())
                        <x-select name="document_id" label="Dokumen ijazah tercatat (opsional)">
                            <option value="">Tidak memilih</option>
                            @foreach ($documents as $d)
                                <option value="{{ $d->id }}" @selected((int) old('document_id') === $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </x-select>
                    @endif

                    <div class="row">
                        <div class="col-md-4"><x-input name="planned_date" type="date" label="Tanggal rencana pengambilan" :value="old('planned_date')" required /></div>
                        <div class="col-md-4"><x-input name="pickup_time_start" type="time" label="Jam mulai (opsional)" :value="old('pickup_time_start')" /></div>
                        <div class="col-md-4"><x-input name="pickup_time_end" type="time" label="Jam selesai (opsional)" :value="old('pickup_time_end')" /></div>
                    </div>

                    <x-input name="contact_phone" label="Kontak yang dapat dihubungi" :value="old('contact_phone', auth()->user()->phone)" required />
                    <x-textarea name="description" label="Catatan tambahan" :value="old('description')" />

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="represented" value="1" id="f_represented" @checked($represented)>
                        <label class="form-check-label" for="f_represented">Ijazah akan diambil oleh orang lain (perwakilan)</label>
                    </div>

                    <div id="proxyFields" @if (! $represented) hidden @endif>
                        <div class="row">
                            <div class="col-md-6"><x-input name="receiver_name" label="Nama pengambil" :value="old('receiver_name')" /></div>
                            <div class="col-md-6"><x-input name="receiver_relation" label="Hubungan dengan siswa" :value="old('receiver_relation')" placeholder="contoh: Ibu, Ayah, Kakak" /></div>
                        </div>
                        <x-file name="power_of_attorney" label="Surat kuasa" hint="Wajib bila diwakilkan. PDF/JPG/PNG, maksimal 2 MB." />
                    </div>

                    <x-file name="attachments" label="Dokumen pendukung (opsional)" :multiple="true" />

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                        <a href="{{ route('student.transactions.index') }}" class="btn btn-outline-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var box = document.getElementById('f_represented');
        var fields = document.getElementById('proxyFields');
        if (!box || !fields) return;
        box.addEventListener('change', function () { fields.hidden = !box.checked; });
    })();
</script>
@endpush
