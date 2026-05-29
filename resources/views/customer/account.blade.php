@extends('customer.layouts.dashboard')

@section('title', 'Profile')

@section('dashboard_content')
<h1 style="font-size:1.25rem;font-weight:900;color:#111827;margin:0 0 20px;">Account settings</h1>

<form action="{{ route('customer.account.update') }}" method="POST" style="background:#fff;border:1.5px solid #e5e7eb;border-radius:18px;padding:24px;max-width:560px;">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">First name</label>
            <input type="text" name="firstname" value="{{ old('firstname', $customer->firstname) }}" required class="co-input" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
        <div>
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Last name</label>
            <input type="text" name="lastname" value="{{ old('lastname', $customer->lastname) }}" required class="co-input" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
    </div>
    <div style="margin-bottom:12px;">
        <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Email</label>
        <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="co-input" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
    </div>
    <div style="margin-bottom:20px;">
        <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Phone</label>
        <input type="text" name="telephone" value="{{ old('telephone', $customer->telephone) }}" class="co-input" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
    </div>

    <div style="border-top:1px solid #f1f5f9;padding-top:20px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:12px;">Change password</div>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 12px;">Leave blank to keep your current password.</p>
        <div style="margin-bottom:12px;">
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">New password</label>
            <input type="password" name="password" class="co-input" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
        <div>
            <label class="co-label" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:5px;">Confirm password</label>
            <input type="password" name="password_confirmation" class="co-input" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 13px;font-size:14px;box-sizing:border-box;">
        </div>
    </div>

    <button type="submit" style="background:#4f46e5;color:#fff;border:none;border-radius:12px;padding:12px 24px;font-size:14px;font-weight:800;cursor:pointer;">Save changes</button>
</form>
@endsection
