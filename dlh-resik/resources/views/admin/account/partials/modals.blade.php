{{-- MODAL: Batas Akun --}}
<div class="modal" id="limitModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">⚠️ Batas Akun Tercapai</div>
            <span class="close-modal" onclick="closeModal('limitModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>Jumlah akun admin sudah mencapai batas maksimal (3).</strong></p>
            <p>Silakan <strong>hapus salah satu akun tambahan</strong> terlebih dahulu jika ingin menambah akun baru.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-small btn-otp" onclick="closeModal('limitModal')">Mengerti</button>
        </div>
    </div>
</div>

{{-- MODAL: Kirim OTP --}}
<div class="modal" id="otpRequestModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="otpModalTitle">Kirim Kode OTP</div>
            <span class="close-modal" onclick="closeModal('otpRequestModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Kode OTP akan dikirim ke email berikut:</p>
            <div style="background:#f0f9f0; padding:10px; border-radius:6px; font-weight:bold; color:#20A726;"
                 id="targetEmailDisplay">
                email@domain.com
            </div>
            <button type="button" class="btn-small btn-otp" onclick="sendOTPToTarget()" style="margin-top:15px;">
                Kirim Kode OTP Sekarang
            </button>
            <div id="otpRequestStatus" style="margin-top:10px;"></div>
        </div>
    </div>
</div>

{{-- MODAL: Verifikasi OTP --}}
<div class="modal" id="otpVerifyModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Masukkan Kode OTP</div>
            <span class="close-modal" onclick="closeModal('otpVerifyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Masukkan kode 4 digit yang dikirim ke <span id="otpTargetEmail" style="font-weight:bold;">email@domain.com</span>.</p>
            <label for="otpInput">Kode OTP</label>
            <input type="text" id="otpInput" maxlength="4" placeholder="1234"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            <div id="otpVerifyStatus"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-small btn-cancel" onclick="closeModal('otpVerifyModal')">Batal</button>
            <button type="button" class="btn-small btn-otp" onclick="verifyOTP()">Verifikasi</button>
        </div>
    </div>
</div>
