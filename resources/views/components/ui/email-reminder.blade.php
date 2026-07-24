@if(!auth()->user()->email)
<div style="background:#fffbeb; border:1px solid #fde68a; border-left:4px solid #d97706;
            border-radius:12px; padding:14px 18px; margin-bottom:16px;
            display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:20px;">📧</span>
        <div>
            <p style="font-size:13px; font-weight:700; color:#b45309; margin-bottom:2px;">
                Email belum ditambahkan
            </p>
            <p style="font-size:12px; color:#92400e;">
                Tambahkan email agar kamu dapat menerima notifikasi tiket melalui email.
            </p>
        </div>
    </div>
    <a href="{{ route('profile.edit') }}"
       style="display:inline-flex; align-items:center; gap:6px; background:#d97706; color:white;
              padding:8px 16px; border-radius:8px; font-size:12px; font-weight:700;
              text-decoration:none; white-space:nowrap; transition:background 0.2s;"
       onmouseover="this.style.background='#b45309'"
       onmouseout="this.style.background='#d97706'">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Tambah Email
    </a>
</div>
@endif
