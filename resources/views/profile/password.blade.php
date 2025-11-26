<h2>Ubah Password</h2>

@if(session('success'))
<p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('profile.password.update') }}">
    @csrf

    <label>Password Lama</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>Password Baru</label><br>
    <input type="password" name="password" required><br><br>

    <label>Konfirmasi Password Baru</label><br>
    <input type="password" name="password_confirmation" required><br><br>

    <button type="submit">Update Password</button>
</form>

<a href="{{ route('profile') }}">Kembali</a>
