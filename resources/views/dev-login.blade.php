@extends('layouts.app')

@section('title', 'Developer Login')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden p-8">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 mx-auto bg-primary/20 border border-primary/30 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">Developer Access</h2>
            <p class="text-slate-400 text-sm mt-1">Authenticate to access administrative tools</p>
        </div>

        <div id="alertBox" class="hidden mb-6 p-4 rounded-lg text-sm border"></div>

        <!-- Email Form -->
        <form id="emailForm" onsubmit="sendOtp(event)" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Developer Email</label>
                <input type="email" id="emailInput" required
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-white outline-none transition-colors"
                       placeholder="Enter your authorized email">
            </div>
            <button type="submit" id="sendBtn"
                    class="w-full py-3 bg-primary hover:bg-opacity-90 text-primary-content font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                <span id="sendBtnText">Send OTP via Email</span>
                <svg id="sendSpinner" class="hidden animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

        <!-- OTP Form -->
        <form id="otpForm" onsubmit="verifyOtp(event)" class="hidden space-y-4 mt-6 pt-6 border-t border-slate-800">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">6-Digit OTP</label>
                <input type="text" id="otpInput" required maxlength="6" pattern="\d{6}"
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary text-white outline-none tracking-widest text-center text-xl transition-colors"
                       placeholder="&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>
            <button type="submit" id="verifyBtn"
                    class="w-full py-3 bg-secondary hover:bg-opacity-90 text-secondary-content font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                <span id="verifyBtnText">Verify & Login</span>
                <svg id="verifySpinner" class="hidden animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

    </div>
</div>

<script>
    const emailForm = document.getElementById('emailForm');
    const otpForm = document.getElementById('otpForm');
    const emailInput = document.getElementById('emailInput');
    const otpInput = document.getElementById('otpInput');
    const alertBox = document.getElementById('alertBox');
    
    // UI Helpers
    function showAlert(message, isError = true) {
        alertBox.classList.remove('hidden', 'bg-error/10', 'border-error/30', 'text-error', 'bg-success/10', 'border-success/30', 'text-success');
        
        if (isError) {
            alertBox.classList.add('bg-error/10', 'border-error/30', 'text-error');
        } else {
            alertBox.classList.add('bg-success/10', 'border-success/30', 'text-success');
        }
        
        alertBox.textContent = message;
    }

    function toggleSpinner(btnPrefix, show) {
        document.getElementById(`${btnPrefix}BtnText`).classList.toggle('hidden', show);
        document.getElementById(`${btnPrefix}Spinner`).classList.toggle('hidden', !show);
        document.getElementById(`${btnPrefix}Btn`).disabled = show;
    }

    async function sendOtp(e) {
        e.preventDefault();
        alertBox.classList.add('hidden');
        toggleSpinner('send', true);

        try {
            const res = await fetch('{{ url("/dev/send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: emailInput.value })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Validation Failed');
            }

            showAlert(data.message, false);
            
            // Switch UI state
            emailInput.disabled = true;
            document.getElementById('sendBtn').classList.add('hidden');
            otpForm.classList.remove('hidden');
            otpInput.focus();

        } catch (error) {
            showAlert(error.message);
        } finally {
            toggleSpinner('send', false);
        }
    }

    async function verifyOtp(e) {
        e.preventDefault();
        alertBox.classList.add('hidden');
        toggleSpinner('verify', true);

        try {
            const res = await fetch('{{ url("/dev/verify-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    email: emailInput.value,
                    otp: otpInput.value 
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Invalid OTP');
            }

            showAlert('Success! Redirecting...', false);
            window.location.href = data.redirect;

        } catch (error) {
            showAlert(error.message);
            toggleSpinner('verify', false);
        }
    }
</script>
@endsection
