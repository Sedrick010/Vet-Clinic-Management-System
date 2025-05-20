        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])

        <!-- Livewire Styles -->
        @livewireStyles
        
        @if(auth()->check() && isset($clinic) && ($clinic->subscription_plan === 'free' || !$clinic->is_subscription_active))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Find all PDF download links and either disable them or add an upgrade prompt
                const pdfLinks = document.querySelectorAll('a[href*="pdf"]');
                
                pdfLinks.forEach(link => {
                    // Check if the link is for downloading a PDF
                    if (link.href.includes('/pdf') || link.textContent.toLowerCase().includes('pdf')) {
                        // Save the original href
                        link.dataset.originalHref = link.href;
                        
                        // Replace the href with javascript:void(0)
                        link.href = 'javascript:void(0)';
                        
                        // Add a class for styling
                        link.classList.add('disabled-pdf-link');
                        
                        // Add click event to show upgrade message
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            alert('PDF export is available only on paid plans (Basic, Standard, and Business). Please upgrade your subscription to access this feature.');
                            window.location.href = '{{ route("subscription.index") }}?upgrade_required=true';
                        });
                        
                        // Add visual indication that this is premium
                        if (!link.querySelector('.premium-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-warning text-dark ms-1 premium-badge';
                            badge.textContent = 'PREMIUM';
                            badge.style.fontSize = '0.65rem';
                            link.appendChild(badge);
                        }
                    }
                });
            });
        </script>
        <style>
            .disabled-pdf-link {
                cursor: not-allowed;
                opacity: 0.7;
            }
            .disabled-pdf-link:hover {
                text-decoration: none !important;
            }
            .premium-badge {
                white-space: nowrap;
                vertical-align: text-top;
            }
        </style>
        @endif
        
        @stack('styles') 