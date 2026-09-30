<div class="d-flex gap-2 mb-3">
    <a class="btn btn-success btn-sm" href="{{ route('reports.export', ['report' => $exportReport, 'format' => 'excel'] + request()->query()) }}"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
    <a class="btn btn-danger btn-sm" href="{{ route('reports.export', ['report' => $exportReport, 'format' => 'pdf'] + request()->query()) }}"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
</div>
