<form action="<?= url('/login') ?>" method="POST" id="loginForm">
    <?= csrf_field() ?>

    <div class="form-group">
        <label class="form-label" for="login">Username or Email</label>
        <input type="text" id="login" name="login" class="form-control" required autofocus placeholder="e.g. owner or name@voyagego.com" value="<?= e($oldLogin ?? $oldEmail ?? '') ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position: relative;">
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••••••" style="padding-right: 2.75rem;">
            <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" title="Show/Hide Password" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px;">
                <svg id="eyeIcon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem; font-size: 1rem;">
        Sign In to Portal
    </button>
</form>

<div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-light);">
    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); margin-bottom: 0.75rem; text-align: center;">
        Quick-Fill Development Credentials
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
        <button type="button" class="btn btn-navy btn-sm" onclick="fillCreds('owner', 'owner123')">
            👑 System Owner
        </button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillCreds('admin', 'Admin@12345')">
            🛡️ Admin Officer
        </button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillCreds('dispatcher', 'Dispatcher@12345')">
            🎯 Dispatcher
        </button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillCreds('rajesh.driver', 'Driver@12345')">
            🚗 Chauffeur
        </button>
    </div>
    <div style="margin-top: 0.75rem; font-size: 0.75rem; color: #64748B; text-align: center;">
        🔒 Default owner password is temporary. Change it immediately under <strong>Users Management</strong>.
    </div>
</div>

<script>
function fillCreds(login, password) {
    document.getElementById('login').value = login;
    document.getElementById('password').value = password;
}

function togglePasswordVisibility() {
    const pwdInput = document.getElementById('password');
    if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
    } else {
        pwdInput.type = 'password';
    }
}
</script>
