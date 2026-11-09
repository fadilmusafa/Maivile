<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('q'));
        $categoryId = $request->integer('category_id') ?: null;

        // Selalu dibatasi pada dokumen milik siswa yang sedang login.
        $documents = Document::query()
            ->where('owner_user_id', $user->id)
            ->with('category:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('document_number', 'like', $like));
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::whereIn('id', Document::where('owner_user_id', $user->id)->select('category_id'))
            ->orderBy('name')->get(['id', 'name']);

        return view('student.documents.index', compact('documents', 'categories', 'search', 'categoryId'));
    }
}
