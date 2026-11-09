@extends('layouts.app')

@section('title', 'Ubah Jenis Layanan')

@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.service-types.update', $serviceType) }}" data-loading novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="code" class="form-label">Kode layanan</label>
                        <input id="code" type="text" class="form-control" value="{{ $serviceType->code }}" disabled>
                        <div class="form-text text-secondary">Kode tidak dapat diubah.</div>
                    </div>

                    <x-input name="name" label="Nama layanan" :value="old('name', $serviceType->name)" required />
                    <x-textarea name="description" label="Keterangan" :value="old('description', $serviceType->description)" />

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="f_is_active" value="1"
                               @checked(old('is_active', $serviceType->is_active))>
                        <label class="form-check-label" for="f_is_active">Layanan aktif (dapat diajukan siswa)</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.service-types.index') }}" class="btn btn-outline-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
