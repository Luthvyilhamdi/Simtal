{{-- Modal import reusable (gaya kalibrasi/penilaian, tanpa halaman khusus).
     Param: $modalId, $title, $cols (HTML), $templateRoute, $actionRoute --}}
@once
@push('styles')
<style>
    .imp-modal { position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);z-index:3000;display:none;align-items:center;justify-content:center; }
    .imp-modal.show { display:flex; }
    .imp-box { background:white;border-radius:16px;padding:24px;width:100%;max-width:440px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:left; }
    .imp-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:12px; }
    .imp-cols { font-size:11px;color:#6b7280;background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:10px 12px;line-height:1.6;margin-bottom:12px; }
    .imp-cols code { background:#eef2ff;color:#4338ca;padding:1px 5px;border-radius:4px;font-size:11px; }
    .imp-tmpl { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#15803d;text-decoration:none;margin-bottom:14px; }
    .imp-file { display:block;width:100%;border:2px dashed #d1d5db;border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:#fafafa;font-size:12px;color:#6b7280;margin-bottom:12px;transition:all .15s; }
    .imp-file:hover { border-color:#15803d;background:#f0fdf4;color:#15803d; }
    .imp-file input[type=file] { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0; }
    .imp-actions { display:flex;gap:8px; }
    .imp-btn { flex:1;padding:10px;border-radius:9px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .15s; }
    .imp-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .imp-btn.green { background:#15803d;color:white; }
    .imp-btn.green:hover { background:#166534; }
</style>
@endpush
@push('scripts')
<script>
    function openImportModal(id){ var m=document.getElementById(id); if(m){ m.classList.add('show'); document.body.style.overflow='hidden'; } }
    function closeImportModal(id){ var m=document.getElementById(id); if(m){ m.classList.remove('show'); document.body.style.overflow=''; } }
    document.addEventListener('click', function(e){ if(e.target.classList && e.target.classList.contains('imp-modal')) closeImportModal(e.target.id); });
    function updateImpFile(input){ if(input.files && input.files[0]) input.closest('label').childNodes[0].textContent = input.files[0].name; }
</script>
@endpush
@endonce

<div class="imp-modal" id="{{ $modalId }}">
    <div class="imp-box">
        <div class="imp-title">📥 {{ $title }}</div>
        <div class="imp-cols">{!! $cols !!}</div>
        <a href="{{ $templateRoute }}" class="imp-tmpl">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#15803d" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template
        </a>
        <form method="POST" action="{{ $actionRoute }}" enctype="multipart/form-data">
            @csrf
            <label class="imp-file">Pilih file Excel/CSV<input type="file" name="file" accept=".xlsx,.xls,.csv" required onchange="updateImpFile(this)"></label>
            <div class="imp-actions">
                <button type="button" class="imp-btn cancel" onclick="closeImportModal('{{ $modalId }}')">Batal</button>
                <button type="submit" class="imp-btn green">Import</button>
            </div>
        </form>
    </div>
</div>
