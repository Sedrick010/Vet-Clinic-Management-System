<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VetClinic') }} - Admin Google Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .clinic-logo {
                max-width: 200px;
                max-height: 150px;
                margin: 0 auto;
                transition: all 0.3s ease;
                object-fit: contain;
            }
            
            .clinic-logo:hover {
                transform: scale(1.05);
            }
            
            .logo-container {
                display: flex;
                justify-content: center;
                margin-bottom: 1.5rem;
                padding: 1rem;
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Navigation -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <a href="{{ route('welcome') }}" class="text-xl font-bold text-primary">
                                    <i class="fas fa-paw me-2"></i> VetClinic System
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-primary text-white px-6 py-4">
                            <h2 class="text-2xl font-bold">Admin Google Sign-In</h2>
                            <p class="text-sm opacity-80 mt-1">Access the admin portal with your Google account</p>
                        </div>
                        
                        <!-- Logo Section -->
                        <div class="px-6 pt-6">
                            <div class="logo-container">
                                <img src="{{ asset('images/logos/default-clinic-logo.png') }}" alt="VetClinic Logo" class="clinic-logo" />
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <!-- Session Status -->
                            @if (session('status'))
                                <div class="mb-4 px-4 py-2 bg-green-100 border-l-4 border-green-500 text-green-700">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- Warning Messages -->
                            @if (session('warning'))
                                <div class="mb-4 px-4 py-2 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                                    {{ session('warning') }}
                                </div>
                            @endif

                            <!-- Domain-specific instructions -->
                            <div class="mb-4 px-4 py-2 bg-blue-100 border-l-4 border-blue-500 text-blue-700">
                                <p class="font-medium">Admin Portal Google Sign-In</p>
                                <p class="text-sm mt-1">This is for system administrators only. Only authorized Google accounts will be granted access.</p>
                            </div>

                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="mb-4 px-4 py-2 bg-red-100 border-l-4 border-red-500 text-red-700">
                                    <p class="font-medium">Oops! There was a problem with your login.</p>
                                    <ul class="mt-2 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-6 mb-6">
                                <div class="text-center">
                                    <button id="googleSignInButton" class="inline-flex items-center justify-center w-full px-4 py-3 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-5 h-5 mr-2">
                                        Sign in with Google
                                    </button>
                                </div>
                                
                                <div class="mt-6 text-center">
                                    <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Standard Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="mb-4 md:mb-0">
                                <h3 class="text-lg font-semibold">VetClinic Management System</h3>
                                <p class="text-gray-600 mt-2">The complete solution for veterinary clinics</p>
                            </div>
                            <div>
                                <p class="text-gray-600">&copy; {{ date('Y') }} VetClinic. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Firebase initialization -->
        <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Firebase with configuration from config file
            const firebaseConfig = {
                apiKey: "{{ config('firebase.api_key') }}",
                authDomain: "{{ config('firebase.auth_domain') }}",
                projectId: "{{ config('firebase.project_id') }}",
                storageBucket: "{{ config('firebase.storage_bucket') }}",
                messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
                appId: "{{ config('firebase.app_id') }}",
                measurementId: "{{ config('firebase.measurement_id') }}"
            };
            
            // Initialize Firebase
            try {
                const app = firebase.initializeApp(firebaseConfig);
                
                // Google sign-in provider
                const googleProvider = new firebase.auth.GoogleAuthProvider();
                googleProvider.addScope('profile');
                googleProvider.addScope('email');
                
                // Handle sign-in button click
                document.getElementById('googleSignInButton').addEventListener('click', function() {
                    firebase.auth().signInWithPopup(googleProvider)
                        .then(function(result) {
                            // The signed-in user info
                            const user = result.user;
                            const idToken = result.credential.idToken;
                            
                            // Send token to backend
                            authenticateWithBackend(user, idToken);
                        })
                        .catch(function(error) {
                            console.error('Error during sign in', error);
                            alert('Authentication failed: ' + error.message);
                        });
                });
                
                // Handle authentication with Laravel backend
                function authenticateWithBackend(user, idToken) {
                    // Create CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Send data to Laravel backend
                    fetch('{{ route("auth.firebase.callback") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name: user.displayName,
                            email: user.email,
                            firebase_uid: user.uid,
                            id_token: idToken
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Authentication failed. You may not have permission to access the admin area.');
                            firebase.auth().signOut();
                        }
                    })
                    .catch(error => {
                        console.error('Error during backend authentication', error);
                        alert('Server error occurred. Please try again.');
                        firebase.auth().signOut();
                    });
                }
            } catch (error) {
                console.error('Firebase initialization error:', error);
                alert('Error initializing Firebase. Please check the console for details.');
                
                // Disable the button if Firebase fails to initialize
                document.getElementById('googleSignInButton').disabled = true;
                document.getElementById('googleSignInButton').innerHTML = 'Google Sign-In Unavailable';
            }
        });
        </script>
    </body>
</html> 