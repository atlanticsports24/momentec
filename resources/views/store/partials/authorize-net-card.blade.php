<div id="authorize-net-card" class="co-card" style="margin-top:14px;display:none;" x-show="paymentCode === 'authorize_net'" x-cloak>
    <div class="co-card-title">
        <div class="co-card-icon" style="background:#eef2ff;">
            <svg width="14" height="14" fill="none" stroke="#4f46e5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        Credit / Debit Card
    </div>
    <div class="co-field">
        <label class="co-label" for="cc_number">Card number *</label>
        <input type="text" name="cc_number" id="cc_number" class="co-input" placeholder="4111111111111111" maxlength="19" autocomplete="cc-number" value="{{ old('cc_number') }}">
    </div>
    <div class="co-row-2">
        <div class="co-field">
            <label class="co-label" for="cc_expire_date_month">Expiry month *</label>
            <select name="cc_expire_date_month" id="cc_expire_date_month" class="co-input">
                <option value="">Month</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" @selected(old('cc_expire_date_month') == sprintf('%02d', $m))>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div class="co-field">
            <label class="co-label" for="cc_expire_date_year">Expiry year *</label>
            <select name="cc_expire_date_year" id="cc_expire_date_year" class="co-input">
                <option value="">Year</option>
                @for ($y = (int) date('Y'); $y < (int) date('Y') + 11; $y++)
                    <option value="{{ $y }}" @selected(old('cc_expire_date_year') == (string) $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="co-field" style="max-width:160px;">
        <label class="co-label" for="cc_cvv2">Card code (CVV) *</label>
        <input type="text" name="cc_cvv2" id="cc_cvv2" class="co-input" placeholder="123" maxlength="4" autocomplete="cc-csc" value="{{ old('cc_cvv2') }}">
    </div>
    <p style="font-size:11px;color:#9ca3af;margin:0;">Payments are processed securely by Authorize.Net.</p>
</div>
