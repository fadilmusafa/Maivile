<div class="card">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
            <form method="GET" class="d-flex flex-wrap gap-2" role="search">
                <input type="hidden" name="role" value="pending">
                <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 260px"
                       placeholder="Cari nama, NIS, email" aria-label="Cari pendaftar">
                <select name="reg" class="form-select" style="max-width: 180px" aria-label="Filter status pendaftaran">
                    <option value="pending" @selected($reg === 'pending')>Menunggu</option>
                    <option value="rejected" @selected($reg === 'rejected')>Ditolak</option>
                </select>
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Cari</button>
                @if ($search !== '' || $reg === 'rejected')
                    <a class="btn btn-link" href="{{ route('admin.users.index', ['role' => 'pending']) }}">Reset</a>
                @endif
            </form>
        </div>

        <p class="text-secondary small">
            Pendaftar tidak bisa masuk sampai disetujui. Periksa NIS, nama, dan kelas dengan data sekolah sebelum menyetujui.
        </p>

        @if ($users->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="bi bi-person-check fs-1 d-block mb-2" aria-hidden="true"></i>
                {{ $reg === 'rejected' ? 'Tidak ada pendaftaran yang ditolak.' : 'Tidak ada pendaftar yang menunggu persetujuan.' }}
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">NIS</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Kontak</th>
                            <th scope="col">Mendaftar</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $u->name }}
                                    @if ($u->isRegistrationRejected())
                                        <div class="small fw-normal text-secondary">
                                            <x-badge color="danger">Ditolak</x-badge>
                                            @if ($u->registration_note) Catatan: {{ $u->registration_note }} @endif
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $u->nis ?? '-' }}</td>
                                <td>{{ $u->class_name ?? '-' }}@if ($u->major) <span class="text-secondary">&middot; {{ $u->major }}</span>@endif</td>
                                <td>
                                    <div>{{ $u->email ?? '-' }}</div>
                                    <div class="small text-secondary">{{ $u->phone ?? '-' }}</div>
                                </td>
                                <td class="text-nowrap">{{ $u->created_at->translatedFormat('d M Y H:i') }}</td>
                                <td class="text-end text-nowrap">
                                    <form method="POST" action="{{ route('admin.users.approve-registration', $u) }}" class="d-inline"
                                          data-confirm="Setujui pendaftaran {{ $u->name }}? Siswa langsung bisa masuk.">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Setujui
                                        </button>
                                    </form>

                                    @if ($u->isRegistrationPending())
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $u->id }}">
                                            <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Tolak
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>

@foreach ($users as $u)
    @if ($u->isRegistrationPending())
        <div class="modal fade" id="rejectModal{{ $u->id }}" tabindex="-1" aria-labelledby="rejectTitle{{ $u->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.users.reject-registration', $u) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="rejectTitle{{ $u->id }}">Tolak pendaftaran {{ $u->name }}</h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <label for="note{{ $u->id }}" class="form-label">Catatan untuk pendaftar <span class="text-secondary" aria-hidden="true">*</span></label>
                        <textarea id="note{{ $u->id }}" name="note" rows="3" maxlength="500" required class="form-control"
                                  placeholder="Contoh: NIS tidak cocok dengan data sekolah."></textarea>
                        <div class="form-text text-secondary">Catatan ini tampil saat pendaftar mencoba masuk.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach
