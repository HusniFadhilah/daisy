@if(Auth::check() && Auth::user()->hasMultipleRoles())
<div class="dropdown">
    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="roleSwitcher" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-badge me-1"></i>
        <span id="currentRoleText">{{ Auth::user()->role_alias }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="roleSwitcher">
        <li class="dropdown-header">Pilih Role Aktif</li>
        @foreach(Auth::user()->available_roles as $role)
        <li>
            <a class="dropdown-item role-switch-item {{ $role['name'] === Auth::user()->role_selected ? 'active' : '' }}" href="#" data-role="{{ $role['name'] }}" onclick="switchRole('{{ $role['name'] }}', event)">
                <i class="bi bi-{{ getRoleIcon($role['name']) }} me-2"></i>
                {{ $role['alias'] }}
                @if($role['name'] === Auth::user()->role_selected)
                <i class="bi bi-check-circle-fill text-success ms-2"></i>
                @endif
            </a>
        </li>
        @endforeach
        <li>
            <hr class="dropdown-divider">
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('select.role') }}">
                <i class="bi bi-gear me-2"></i>
                Kelola Role
            </a>
        </li>
    </ul>
</div>

@push('scripts')
<script>
    async function switchRole(role, event) {
        event.preventDefault();

        try {
            const response = await fetch('{{ route("profile.switch-role") }}', {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    role: role
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                document.getElementById('currentRoleText').textContent = data.role_alias;

                // Remove active from all
                document.querySelectorAll('.role-switch-item').forEach(item => {
                    item.classList.remove('active');

                    let checkIcon = item.querySelector('.bi-check-circle-fill');
                    if (checkIcon !== null) checkIcon.parentNode.removeChild(checkIcon);
                });


                // Add active to selected
                event.target.closest('.role-switch-item').classList.add('active');
                event.target.closest('.role-switch-item').innerHTML += ' <i class="bi bi-check-circle-fill text-success ms-2"></i>';

                // Show success message
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: data.message
                        , timer: 2000
                        , showConfirmButton: false
                    });
                }

                // Reload page to apply new role
                window.location.href = "{{ route('dashboard') }}";
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error switching role:', error);
            alert('Gagal mengubah role: ' + error.message);
        }
    }

</script>
@endpush
@endif

@php
function getRoleIcon($roleName) {
$icons = [
'super_admin' => 'shield-fill-check',
'sekretariat' => 'person-badge',
'asesor' => 'clipboard-check',
'validator' => 'check2-circle',
'verifikator' => 'shield-check',
'admin_univ' => 'building',
'admin_prodi' => 'mortarboard',
'default' => 'person',
];
return $icons[$roleName] ?? 'person';
}
@endphp
