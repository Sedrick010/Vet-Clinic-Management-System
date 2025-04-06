<x-guest-layout>
    <div class="py-4">
        @if(session('success'))
        <div id="success-alert" class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md relative" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>

        <!-- Success Modal - Will show automatically -->
        <div id="successModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Success
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {!! session('success') !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="closeSuccessModal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @php
            $status = Session::get('pending_clinic_status', 'pending');
            $clinicName = Session::get('pending_clinic_name', 'Your clinic');
            $ownerName = Session::get('pending_clinic_owner');
            $contactEmail = Session::get('pending_clinic_email');
            $subdomain = Session::get('pending_clinic_subdomain');
        @endphp

        @if($status === 'approved')
            <!-- Approved Status -->
            <h2 class="text-center text-2xl font-bold text-green-600 mb-5">Registration Approved!</h2>
            
            <div class="flex justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h3 class="text-xl font-medium text-center text-gray-800 mb-4">Your Clinic Has Been Approved!</h3>
        @elseif($status === 'rejected')
            <!-- Rejected Status -->
            <h2 class="text-center text-2xl font-bold text-red-600 mb-5">Registration Rejected</h2>
            
            <div class="flex justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h3 class="text-xl font-medium text-center text-gray-800 mb-4">Your Clinic Registration Was Not Approved</h3>
        @else
            <!-- Pending Status (Default) -->
            <h2 class="text-center text-2xl font-bold text-yellow-600 mb-5">Registration Pending</h2>
            
            <div class="flex justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h3 class="text-xl font-medium text-center text-gray-800 mb-4">Your Clinic Registration is Pending Approval</h3>
        @endif
        
        <!-- Clinic Information Box -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
            <h4 class="font-semibold text-blue-800 mb-2">Clinic Information</h4>
            <div class="text-blue-700">
                <p><span class="font-medium">Clinic Name:</span> {{ $clinicName }}</p>
                @if($ownerName)
                <p><span class="font-medium">Owner:</span> {{ $ownerName }}</p>
                @endif
                @if($contactEmail)
                <p><span class="font-medium">Contact Email:</span> {{ $contactEmail }}</p>
                @endif
                @if($subdomain)
                <p><span class="font-medium">Subdomain:</span> {{ $subdomain }}</p>
                @endif
            </div>
        </div>
        
        @if($status === 'approved')
            <div class="bg-green-50 border border-green-100 rounded-lg p-4 mb-6">
                <p class="text-gray-700 mb-2 text-center">Your clinic has been approved and is now ready to use!</p>
                <div class="text-center mt-4">
                    <a href="{{ config('app.url') }}/login" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Your Clinic
                    </a>
                </div>
            </div>
            
            <p class="text-gray-600 mb-4 text-center">
                You can now access your clinic using your registered subdomain:
            </p>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6 text-center">
                <p class="text-lg font-medium text-primary">
                    <a href="{{ $protocol ?? 'https' }}://{{ $subdomain }}.{{ Str::after(config('app.url'), 'http://') }}" class="hover:underline">
                        {{ $subdomain }}.{{ Str::after(config('app.url'), 'http://') }}
                    </a>
                </p>
            </div>
            
        @elseif($status === 'rejected')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-gray-700 font-semibold mb-2">Reason for rejection:</p>
                <p class="text-gray-600">{{ Session::get('pending_clinic_reason', 'Your clinic registration did not meet our requirements.') }}</p>
            </div>
            
            <p class="text-gray-600 mb-6 text-center">
                We're sorry, but your clinic registration has been rejected. You can update your information and resubmit your application.
            </p>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <p class="text-gray-700 mb-2"><span class="font-semibold">What you can do next:</span></p>
                <ul class="text-gray-600 list-disc list-inside space-y-2">
                    <li>Review the reason for rejection</li>
                    <li>Make necessary changes to your application</li>
                    <li>Submit a new registration</li>
                    <li>Contact our support team if you need assistance</li>
                </ul>
            </div>
            
        @else
            <p class="text-gray-600 mb-6 text-center">
                Thank you for registering your veterinary clinic with our system! Your registration has been submitted successfully and is now pending review by our administrators.
            </p>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <p class="text-gray-700 mb-2"><span class="font-semibold">What happens next?</span></p>
                <ul class="text-gray-600 list-disc list-inside space-y-2">
                    <li>Our administrative team will review your application</li>
                    <li>You will receive an email notification at <strong>{{ $contactEmail }}</strong> once your registration is approved</li>
                    <li>Once approved, you can login using your email and password to access your clinic dashboard</li>
                </ul>
            </div>
            
            <p class="text-gray-600 mb-8 text-center">
                This process typically takes 1-2 business days. If you have any questions or need assistance, 
                please contact our support team.
            </p>
        @endif
        
        <div class="flex justify-center">
            <a href="{{ route('welcome') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Return to Home Page
            </a>
        </div>
    </div>

    @if(session('success'))
    <script>
        // Show the success modal when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Showing success modal"); // Debug
            let successModal = document.getElementById('successModal');
            if (successModal) {
                // Ensure modal is visible by setting display style directly
                successModal.classList.remove('hidden');
                successModal.style.display = 'block';
                
                // Add event listener to close button
                let closeBtn = document.getElementById('closeSuccessModal');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        successModal.classList.add('hidden');
                        successModal.style.display = 'none';
                    });
                }
                
                // Close when clicking outside the modal content
                successModal.addEventListener('click', function(e) {
                    if (e.target === successModal) {
                        successModal.classList.add('hidden');
                        successModal.style.display = 'none';
                    }
                });
                
                // Auto-hide the modal after 6 seconds
                setTimeout(function() {
                    successModal.classList.add('hidden');
                    successModal.style.display = 'none';
                }, 6000);
            } else {
                console.error("Success modal not found");
            }
        });
    </script>
    @endif
</x-guest-layout> 