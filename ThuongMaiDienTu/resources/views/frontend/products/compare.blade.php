@extends('layouts.app')
@section('title', 'So sánh sản phẩm - DIENMAYPRO')

@push('styles')
<style>
.cmp-page { padding: 20px 0 100px; }
.cmp-breadcrumb { display:flex; align-items:center; gap:6px; font-size:13px; color:#666; margin-bottom:20px; }
.cmp-breadcrumb a { color:#0046ab; }
.cmp-breadcrumb a:hover { text-decoration:underline; }

/* === EMPTY STATE === */
.cmp-empty { background:linear-gradient(135deg,#f8faff,#eef2ff); border-radius:20px; padding:80px 40px; text-align:center; border:1px solid #e0e7ff; }
.cmp-empty-icon { width:100px; height:100px; margin:0 auto 24px; background:linear-gradient(135deg,#0046ab,#003380); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 12px 30px rgba(0,70,171,.25); }
.cmp-empty-icon i { font-size:40px; color:#fff; }
.cmp-empty h2 { font-size:24px; font-weight:800; color:#1a1a2e; margin-bottom:10px; }
.cmp-empty p { font-size:15px; color:#666; margin-bottom:28px; max-width:400px; margin-left:auto; margin-right:auto; line-height:1.6; }
.cmp-empty-btn { display:inline-flex; align-items:center; gap:8px; padding:14px 32px; background:linear-gradient(135deg,#0046ab,#003380); color:#fff; border-radius:10px; font-weight:700; font-size:14px; transition:.3s; box-shadow:0 6px 20px rgba(0,70,171,.25); }
.cmp-empty-btn:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(0,70,171,.35); }

/* === MAIN WRAPPER === */
.cmp-wrapper { --cols:2; }

/* === STICKY PRODUCT HEADER === */
.cmp-header { display:grid; grid-template-columns:170px repeat(var(--cols),1fr); border-bottom:1px solid #f0f0f0; }
.cmp-header-label { padding:20px 16px; display:flex; align-items:center; font-size:13px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.5px; background:#fafbfc; border-right:1px solid #f0f0f0; }

.cmp-prod-card { padding:30px 20px 20px; display:flex; flex-direction:column; align-items:center; gap:12px; border-right:1px solid #f0f0f0; position:relative; transition:.3s ease; background:#fff; }
.cmp-prod-card:last-child { border-right:none; }
.cmp-prod-card:hover { background:#f8fbff; }
.cmp-remove { position:absolute; top:12px; right:12px; width:32px; height:32px; border-radius:50%; border:none; background:#f1f5f9; color:#94a3b8; font-size:14px; cursor:pointer; transition:all .2s ease; display:flex; align-items:center; justify-content:center; z-index:2; }
.cmp-remove:hover { background:#fee2e2; color:#d70018; transform:scale(1.1) rotate(90deg); }
.cmp-prod-img { width:160px; height:160px; object-fit:contain; border-radius:12px; transition:transform .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.cmp-prod-card:hover .cmp-prod-img { transform:scale(1.08) translateY(-5px); }
.cmp-prod-name { font-size:15px; font-weight:700; color:#1e293b; text-align:center; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; transition:.2s; }
.cmp-prod-name:hover { color:#0046ab; }
.cmp-price-box { text-align:center; margin-top:auto; }
.cmp-price { font-size:22px; font-weight:800; color:#d70018; letter-spacing:-0.5px; }
.cmp-old-price { font-size:13px; color:#94a3b8; text-decoration:line-through; margin-top:4px; }
.cmp-discount { display:inline-block; background:#fef2f2; color:#ef4444; font-size:11px; font-weight:800; padding:3px 8px; border-radius:6px; margin-top:6px; border:1px solid #fecaca; }
.cmp-buy-btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px 16px; background:linear-gradient(to right, #0046ab, #003380); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all .3s ease; box-shadow:0 4px 12px rgba(0,70,171,.15); margin-top:8px; }
.cmp-buy-btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,70,171,.3); background:linear-gradient(to right, #0052cc, #003d99); }

/* Add slot */
.cmp-add-slot { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:16px; padding:30px 20px; border-right:1px solid #f0f0f0; cursor:pointer; transition:all .3s ease; min-height:300px; color:#94a3b8; background:#f8fafc; }
.cmp-add-slot:hover { background:#f0f7ff; color:#0046ab; box-shadow:inset 0 0 0 2px #bae6fd; }
.cmp-add-slot i.cmp-add-icon { font-size:48px; width:80px; height:80px; border:2px dashed #cbd5e1; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:all .3s ease; background:#fff; color:#cbd5e1; }
.cmp-add-slot:hover i.cmp-add-icon { border-style:solid; border-color:#0046ab; transform:scale(1.1); color:#0046ab; background:#e0f2fe; box-shadow:0 10px 20px rgba(0,70,171,.1); }
.cmp-add-slot span { font-size:15px; font-weight:600; }
.cmp-add-slot:last-child { border-right:none; }

/* === COMPACT STICKY HEADER === */
.cmp-sticky { position:sticky; top:58px; z-index:50; background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:visible; margin-bottom:0; transition:all .3s ease; }
.cmp-sticky.is-compact .cmp-prod-card { padding:14px 20px; min-height:60px; justify-content:center; }
.cmp-sticky.is-compact .cmp-prod-img { display:none; }
.cmp-sticky.is-compact .cmp-price-box { display:none; }
.cmp-sticky.is-compact .cmp-buy-btn { display:none; }
.cmp-sticky.is-compact .cmp-prod-name { font-size:14px; -webkit-line-clamp:1; margin-bottom:0; }
.cmp-sticky.is-compact .cmp-remove { top:50%; right:10px; transform:translateY(-50%); width:24px; height:24px; font-size:11px; }
.cmp-sticky.is-compact .cmp-add-slot { padding:14px; min-height:60px; flex-direction:row; gap:10px; }
.cmp-sticky.is-compact .cmp-add-slot i.cmp-add-icon { width:32px; height:32px; font-size:16px; }
.cmp-sticky.is-compact .cmp-header-label { padding:14px 16px; }

/* Search inline */
.cmp-inline-search { width:90%; position:relative; display:none; }
.cmp-inline-search input { width:100%; padding:10px 14px; border:2px solid #e5e7eb; border-radius:8px; font-size:13px; outline:none; }
.cmp-inline-search input:focus { border-color:#0046ab; }
.cmp-inline-results { position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #e5e7eb; border-radius:0 0 10px 10px; max-height:220px; overflow-y:auto; z-index:60; display:none; box-shadow:0 8px 24px rgba(0,0,0,.12); }
.cmp-inline-results.show { display:block; }
.cmp-sr-item { display:flex; align-items:center; gap:10px; padding:10px 12px; cursor:pointer; transition:.15s; border-bottom:1px solid #f5f5f5; }
.cmp-sr-item:hover { background:#f0f7ff; }
.cmp-sr-item img { width:36px; height:36px; object-fit:contain; border-radius:4px; }
.cmp-sr-item-name { flex:1; font-size:12px; font-weight:600; color:#333; }
.cmp-sr-item-price { font-size:11px; font-weight:700; color:#d70018; }

/* === TOGGLE BAR === */
.cmp-toggle { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:linear-gradient(135deg,#f8faff,#eef4ff); border-left:4px solid #0046ab; margin-top:-1px; }
.cmp-toggle h3 { font-size:15px; font-weight:700; color:#1a1a2e; display:flex; align-items:center; gap:8px; }
.cmp-toggle-right { display:flex; align-items:center; gap:10px; }
.cmp-toggle-label { font-size:13px; font-weight:600; color:#555; }
.tgl { position:relative; width:46px; height:24px; cursor:pointer; }
.tgl input { display:none; }
.tgl-slider { position:absolute; inset:0; background:#cbd5e1; border-radius:24px; transition:.3s; }
.tgl-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.tgl input:checked+.tgl-slider { background:linear-gradient(135deg,#0046ab,#003380); }
.tgl input:checked+.tgl-slider::before { transform:translateX(22px); }

/* === SPECS TABLE === */
.cmp-table { background:#fff; border-radius:0 0 20px 20px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.04); }
.cmp-row { display:grid; grid-template-columns:170px repeat(var(--cols),1fr); border-bottom:1px solid #f1f5f9; transition:all .4s ease; overflow:hidden; }
.cmp-row:last-child { border-bottom:none; }
.cmp-row:hover { background:#f8fafc; }
.cmp-row.diff .cmp-val { background-color:#f0f9ff; }
.cmp-row.diff .cmp-label { background-color:#e0f2fe; border-left:4px solid #0ea5e9; color:#0369a1; }
.cmp-row.hidden-row { max-height:0; opacity:0; border-bottom:none; padding:0; }

.cmp-label { padding:16px 20px; font-size:14px; font-weight:600; color:#475569; background:#f8fafc; border-right:1px solid #f1f5f9; display:flex; align-items:center; transition:all .3s ease; }
.cmp-val { padding:16px 20px; font-size:14px; color:#1e293b; border-right:1px solid #f1f5f9; display:flex; align-items:center; transition:all .3s ease; line-height:1.6; }
.cmp-val:last-child { border-right:none; }
.cmp-val-empty { color:#94a3b8; font-style:italic; font-size:13px; }

/* No data */
.cmp-no-data { text-align:center; padding:50px 20px; color:#888; }
.cmp-no-data i { font-size:32px; color:#cbd5e1; margin-bottom:12px; display:block; }

/* Toast */
.cmp-toast { position:fixed; top:80px; right:20px; z-index:10000; padding:14px 24px; border-radius:10px; font-size:14px; font-weight:600; display:flex; align-items:center; gap:10px; box-shadow:0 8px 24px rgba(0,0,0,.15); transform:translateX(120%); transition:transform .4s cubic-bezier(.4,0,.2,1); }
.cmp-toast.show { transform:translateX(0); }
.cmp-toast.ok { background:#16a34a; color:#fff; }
.cmp-toast.err { background:#d70018; color:#fff; }
</style>
@endpush

@section('content')
<div class="container cmp-page">
    <nav class="cmp-breadcrumb">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <i class="fa-solid fa-angle-right" style="font-size:10px;color:#bbb"></i>
        <span>So sánh sản phẩm</span>
    </nav>

    @if($products->isEmpty())
        <div class="cmp-empty">
            <div class="cmp-empty-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <h2>Chưa có sản phẩm nào để so sánh</h2>
            <p>Hãy thêm ít nhất 2 sản phẩm cùng danh mục vào khay so sánh để xem chi tiết thông số kỹ thuật.</p>
            <a href="{{ route('products.index') }}" class="cmp-empty-btn"><i class="fa-solid fa-arrow-left"></i> Khám phá sản phẩm</a>
        </div>
    @else
        @php $cols = $products->count(); @endphp
        <div class="cmp-wrapper" style="--cols:3;">

            {{-- Sticky Header --}}
            <div class="cmp-sticky">
                <div class="cmp-header">
                    <div class="cmp-header-label"><i class="fa-solid fa-scale-balanced" style="margin-right:6px;color:#0046ab"></i> Sản phẩm</div>
                    @foreach($products as $p)
                        <div class="cmp-prod-card">
                            <button class="cmp-remove" onclick="cmpRemoveReload({{ $p->product_id }})" title="Xóa"><i class="fa-solid fa-xmark"></i></button>
                            <a href="{{ route('product.show', $p->product_id) }}">
                                <img class="cmp-prod-img" src="{{ $p->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}" alt="{{ $p->name }}">
                            </a>
                            <a href="{{ route('product.show', $p->product_id) }}" class="cmp-prod-name">{{ $p->name }}</a>
                            <div class="cmp-price-box">
                                <div class="cmp-price">{{ number_format($p->base_price,0,',','.') }}đ</div>
                                @if($p->old_price && $p->old_price > $p->base_price)
                                    <div class="cmp-old-price">{{ number_format($p->old_price,0,',','.') }}đ</div>
                                    <span class="cmp-discount">-{{ round(($p->old_price - $p->base_price)/$p->old_price*100) }}%</span>
                                @endif
                            </div>
                            <button class="cmp-buy-btn"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
                        </div>
                    @endforeach
                    @for($i = $cols; $i < 3; $i++)
                        <div class="cmp-add-slot" onclick="cmpOpenSearch(this)">
                            <i class="fa-solid fa-plus cmp-add-icon"></i>
                            <span>Thêm sản phẩm</span>
                            <div class="cmp-inline-search" onclick="event.stopPropagation();">
                                <input type="text" placeholder="Nhập tên sản phẩm..." oninput="cmpSearch(this)">
                                <div class="cmp-inline-results"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Toggle --}}
            <div class="cmp-toggle">
                <h3><i class="fa-solid fa-microchip" style="color:#0046ab"></i> Thông số kỹ thuật chi tiết</h3>
                <div class="cmp-toggle-right">
                    <span class="cmp-toggle-label">Chỉ xem điểm khác biệt</span>
                    <label class="tgl"><input type="checkbox" id="tglDiff" onchange="cmpToggle()"><span class="tgl-slider"></span></label>
                </div>
            </div>

            {{-- Specs --}}
            <div class="cmp-table">
                @forelse($comparisonData as $row)
                    <div class="cmp-row {{ $row['is_different'] ? 'diff' : '' }}" data-diff="{{ $row['is_different'] ? '1' : '0' }}">
                        <div class="cmp-label">{{ $row['label'] }}</div>
                        @foreach($row['values'] as $val)
                            <div class="cmp-val {{ $val === '—' ? 'cmp-val-empty' : '' }}">{{ $val === '—' ? 'Không có' : $val }}</div>
                        @endforeach
                        @for($i = count($row['values']); $i < 3; $i++)
                            <div class="cmp-val cmp-val-empty" style="background:#f8fafc; opacity:0.6;"></div>
                        @endfor
                    </div>
                @empty
                    <div class="cmp-no-data">
                        <i class="fa-solid fa-database"></i>
                        <p>Chưa có dữ liệu thông số kỹ thuật để so sánh.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
<div class="cmp-toast" id="cmpToast"><i></i><span id="cmpToastMsg"></span></div>
@endsection

@push('scripts')
<script>
const CMP_CSRF='{{ csrf_token() }}';
const CMP_CAT={{ $products->first()->category_id ?? 'null' }};
const CMP_EX={!! json_encode($products->pluck('product_id')->toArray()) !!};

function cmpToggle(){
    const on=document.getElementById('tglDiff').checked;
    document.querySelectorAll('.cmp-row[data-diff="0"]').forEach(r=>r.classList.toggle('hidden-row',on));
}
function cmpRemoveReload(id){
    fetch(`/compare/remove/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CMP_CSRF,'Accept':'application/json'}}).then(()=>location.reload());
}
function cmpOpenSearch(slot){
    const box=slot.querySelector('.cmp-inline-search');
    if(box){box.style.display='block';box.querySelector('input').focus();}
}
let cmpST;
function cmpSearch(inp){
    clearTimeout(cmpST);
    const kw=inp.value.trim(),rb=inp.parentElement.querySelector('.cmp-inline-results');
    if(kw.length<2){rb.classList.remove('show');return;}
    cmpST=setTimeout(()=>{
        let u=`/api/products/search-compare?keyword=${encodeURIComponent(kw)}&exclude=${CMP_EX.join(',')}`;
        if(CMP_CAT)u+=`&category_id=${CMP_CAT}`;
        fetch(u).then(r=>r.json()).then(ps=>{
            rb.innerHTML=ps.length?ps.map(p=>`<div class="cmp-sr-item" onclick="cmpAdd(${p.product_id})"><img src="${p.thumbnail||'https://via.placeholder.com/40'}"><span class="cmp-sr-item-name">${p.name}</span><span class="cmp-sr-item-price">${Number(p.base_price).toLocaleString('vi-VN')}đ</span></div>`).join(''):'<div style="padding:16px;text-align:center;color:#aaa;font-size:13px">Không tìm thấy</div>';
            rb.classList.add('show');
        });
    },300);
}
function cmpAdd(id){
    fetch('/compare/add',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CMP_CSRF,'Accept':'application/json'},body:JSON.stringify({product_id:id})})
    .then(r=>r.json()).then(d=>{if(d.success)location.reload();else cmpToast(d.message,'err');});
}
function cmpToast(msg,t='ok'){
    const el=document.getElementById('cmpToast');
    el.className='cmp-toast '+t;el.querySelector('i').className=t==='ok'?'fa-solid fa-circle-check':'fa-solid fa-circle-exclamation';
    document.getElementById('cmpToastMsg').textContent=msg;el.classList.add('show');setTimeout(()=>el.classList.remove('show'),3000);
}
document.addEventListener('click',e=>{if(!e.target.closest('.cmp-inline-search')&&!e.target.closest('.cmp-add-slot')){
    document.querySelectorAll('.cmp-inline-search').forEach(el=>el.style.display='none');
    document.querySelectorAll('.cmp-inline-results').forEach(el=>el.classList.remove('show'));
}});

window.addEventListener('scroll', () => {
    const sticky = document.querySelector('.cmp-sticky');
    if (sticky) {
        if (window.scrollY > 220) {
            sticky.classList.add('is-compact');
        } else {
            sticky.classList.remove('is-compact');
        }
    }
});
</script>
@endpush
