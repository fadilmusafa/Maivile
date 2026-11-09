@extends('layouts.app')

@php $isEdit = $category->exists; @endphp

@section('title', $isEdit ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" data-loading novalidate>
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <x-input name="name" label="Nama kategori" :value="old('name', $category->name)" required />
                    <x-textarea name="description" label="Keterangan" :value="old('description', $category->description)" />

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
