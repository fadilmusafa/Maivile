<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceTypeRequest;
use App\Models\ServiceType;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Hanya index/edit/update: tiga layanan inti (kode tetap) memiliki alur status yang tertulis di kode,
 * sehingga menambah/menghapus layanan lewat form tidak akan menghasilkan alur yang valid.
 */
class ServiceTypeController extends Controller
{
    public function index(): View
    {
        $serviceTypes = ServiceType::query()->withCount('transactions')->orderBy('id')->get();

        return view('admin.service-types.index', compact('serviceTypes'));
    }

    public function edit(ServiceType $serviceType): View
    {
        return view('admin.service-types.edit', compact('serviceType'));
    }

    public function update(ServiceTypeRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $serviceType->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLogger::log('service_type_updated', $serviceType, "Layanan {$serviceType->name} diperbarui");

        return redirect()->route('admin.service-types.index')->with('success', 'Jenis layanan berhasil diperbarui.');
    }
}
