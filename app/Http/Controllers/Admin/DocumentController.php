<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentAvailability;
use App\Enums\DocumentKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\DocumentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documents)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $kind = DocumentKind::tryFrom((string) $request->query('kind'));
        $availability = DocumentAvailability::tryFrom((string) $request->query('availability'));
        $categoryId = $request->integer('category_id') ?: null;

        $documents = Document::query()
            ->with(['category:id,name', 'owner:id,name,nis'])
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('document_number', 'like', $like)
                    ->orWhereHas('owner', fn ($o) => $o->where('name', 'like', $like)->orWhere('nis', 'like', $like)));
            })
            ->when($kind, fn ($q) => $q->where('document_kind', $kind->value))
            ->when($availability, fn ($q) => $q->where('availability', $availability->value))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.documents.index', [
            'documents' => $documents,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'kind' => $kind?->value,
            'availability' => $availability?->value,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(): View
    {
        return view('admin.documents.form', $this->formData(new Document([
            'document_kind' => DocumentKind::Physical->value,
        ])));
    }

    public function store(DocumentRequest $request): RedirectResponse
    {
        $document = $this->documents->create($request->safe()->except('file'), $request->file('file'));

        ActivityLogger::log('document_created', $document, "Dokumen {$document->name} ditambahkan");

        return redirect()->route('admin.documents.show', $document)->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function show(Document $document): View
    {
        $document->load(['category:id,name', 'owner:id,name,nis']);
        $transactionCount = $document->transactions()->count();

        return view('admin.documents.show', compact('document', 'transactionCount'));
    }

    public function edit(Document $document): View
    {
        return view('admin.documents.form', $this->formData($document));
    }

    public function update(DocumentRequest $request, Document $document): RedirectResponse
    {
        $this->documents->update($document, $request->safe()->except('file'), $request->file('file'));

        ActivityLogger::log('document_updated', $document, "Dokumen {$document->name} diperbarui");

        return redirect()->route('admin.documents.show', $document)->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        try {
            $this->documents->delete($document);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLogger::log('document_deleted', $document, "Dokumen {$document->name} dihapus");

        return redirect()->route('admin.documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    private function formData(Document $document): array
    {
        return [
            'document' => $document,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'students' => User::students()->orderBy('name')->get(['id', 'name', 'nis']),
        ];
    }
}
