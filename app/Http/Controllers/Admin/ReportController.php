<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportFilterRequest;
use App\Models\ServiceType;
use App\Services\ActivityLogger;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports)
    {
    }

    public function index(ReportFilterRequest $request): View
    {
        $f = $request->filters();

        return view('admin.reports.index', [
            'f' => $f,
            'query' => $this->reports->queryString($f),
            'summary' => $this->reports->summary($f),
            'transactions' => $this->reports->listQuery($f)->paginate(15)->withQueryString(),
            'serviceTypes' => ServiceType::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Halaman siap cetak (Ctrl+P / tombol Cetak). */
    public function print(ReportFilterRequest $request): View
    {
        ActivityLogger::log('report.print', null, 'Mencetak laporan pengajuan.');

        return view('reports.document', $this->documentData($request->filters(), 'print'));
    }

    public function pdf(ReportFilterRequest $request)
    {
        $data = $this->documentData($request->filters(), 'pdf');
        ActivityLogger::log('report.pdf', null, 'Mengunduh laporan pengajuan (PDF).');

        return Pdf::loadView('reports.document', $data)
            ->setPaper('a4', 'landscape')
            ->download('laporan-pengajuan-'.now()->format('Ymd').'.pdf');
    }

    public function excel(ReportFilterRequest $request)
    {
        ActivityLogger::log('report.excel', null, 'Mengunduh laporan pengajuan (Excel).');

        return Excel::download(
            new TransactionsExport($this->reports->listQuery($request->filters())),
            'laporan-pengajuan-'.now()->format('Ymd').'.xlsx'
        );
    }

    private function documentData(array $f, string $mode): array
    {
        $summary = $this->reports->summary($f);

        return [
            'mode' => $mode,
            'summary' => $summary,
            'filters' => $this->reports->describeFilters($f),
            'transactions' => $this->reports->listQuery($f)->limit(ReportService::DOCUMENT_LIMIT)->get(),
            'truncated' => $summary['total'] > ReportService::DOCUMENT_LIMIT,
            'limit' => ReportService::DOCUMENT_LIMIT,
            'printedBy' => auth()->user()->name,
            'printedAt' => now(),
            'backUrl' => route('admin.reports.index', $this->reports->queryString($f)),
        ];
    }
}
