<h2>Settings</h2>

@if(session('success'))
<p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('settings.update') }}">
    @csrf

    <label>Nama Situs</label><br>
    <input type="text" name="site_name" value="My Website" required><br><br>

    <label>Email Notifikasi</label>
    <input type="checkbox" name="email_notifications" value="1"><br><br>

    <button type="submit">Simpan Pengaturan</button>
</form>

<a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
