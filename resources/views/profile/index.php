<h1>Profil Saya</h1>

@if(session('success'))
<p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('profile.update') }}">
    @csrf

    <label>Nama</label><br>
    <input type="text" name="name" value="{{ $user->name }}" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" value="{{ $user->email }}" required><br><br>

    <label>Password Baru (opsional)</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Update Profil</button>
</form>

<br>
<a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
