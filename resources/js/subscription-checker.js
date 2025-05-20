/**
 * Client-side subscription status checker
 * Periodically checks if the subscription status has changed and redirects if necessary
 */
class SubscriptionChecker {
    constructor(options = {}) {
        this.options = {
            checkInterval: options.checkInterval || 30000, // Default: check every 30 seconds
            checkEndpoint: options.checkEndpoint || '/check-subscription-status',
            dashboardUrl: options.dashboardUrl || '/dashboard',
            enabled: options.enabled !== undefined ? options.enabled : true,
            debug: options.debug || false
        };
        
        this.intervalId = null;
        this.isPremiumPage = this.checkIfPremiumPage();
    }

    /**
     * Initialize the subscription checker
     */
    init() {
        if (!this.options.enabled) {
            return;
        }

        // Don't check if not on a premium page
        if (!this.isPremiumPage) {
            this.log('Not a premium page, subscription checker disabled');
            return;
        }
        
        this.log('Subscription checker initialized');
        
        // Start the interval check
        this.intervalId = setInterval(() => {
            this.checkSubscriptionStatus();
        }, this.options.checkInterval);
        
        // Also check immediately on load
        setTimeout(() => {
            this.checkSubscriptionStatus();
        }, 1000);
    }

    /**
     * Check if current page is a premium feature page
     */
    checkIfPremiumPage() {
        const path = window.location.pathname;
        const premiumPaths = ['/premium', '/premium/reports', '/premium/analytics'];
        
        return premiumPaths.some(prefix => path.startsWith(prefix));
    }

    /**
     * Check subscription status via AJAX
     */
    checkSubscriptionStatus() {
        this.log('Checking subscription status...');
        
        fetch(this.options.checkEndpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            this.log('Subscription status response:', data);
            
            // If subscription is not active, redirect to dashboard
            if (!data.status) {
                this.log('Subscription inactive, redirecting to dashboard');
                this.showRedirectMessage();
                setTimeout(() => {
                    window.location.href = this.options.dashboardUrl;
                }, 2000);
            }
        })
        .catch(error => {
            this.log('Error checking subscription status:', error);
            // If there's an error (like a 403), it might mean the user lost access
            if (error.status === 403) {
                window.location.href = this.options.dashboardUrl;
            }
        });
    }

    /**
     * Show a message to the user about redirection
     */
    showRedirectMessage() {
        const messageContainer = document.createElement('div');
        messageContainer.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50';
        messageContainer.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Subscription Status Changed</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Your subscription has been deactivated or expired. Redirecting to dashboard...
                    </p>
                </div>
            </div>
        `;
        document.body.appendChild(messageContainer);
    }

    /**
     * Log messages (only if debug is enabled)
     */
    log(...args) {
        if (this.options.debug) {
            console.log('[Subscription Checker]', ...args);
        }
    }

    /**
     * Stop checking
     */
    stop() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            this.log('Subscription checker stopped');
        }
    }
}

// Initialize the subscription checker if the page has loaded
document.addEventListener('DOMContentLoaded', () => {
    window.subscriptionChecker = new SubscriptionChecker({
        debug: false,
        checkInterval: 20000 // Check every 20 seconds
    });
    window.subscriptionChecker.init();
});

export default SubscriptionChecker; 