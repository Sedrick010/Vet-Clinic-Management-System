/**
 * Update checker module - periodically checks for system updates
 */
class UpdateChecker {
    constructor() {
        this.checkInterval = 6 * 60 * 60 * 1000; // Check every 6 hours
        this.lastChecked = localStorage.getItem('lastUpdateCheck') 
            ? parseInt(localStorage.getItem('lastUpdateCheck')) 
            : 0;
        this.pendingUpdates = [];
        this.notificationDisplayed = false;
    }

    /**
     * Initialize the update checker
     */
    init() {
        // Check if we should check for updates
        const now = Date.now();
        if (now - this.lastChecked > this.checkInterval) {
            // Check for updates after a short delay
            setTimeout(() => this.checkForUpdates(), 30000);
        }

        // Setup interval for periodic checks
        setInterval(() => this.checkForUpdates(), this.checkInterval);

        // Listen for user interaction to show notifications
        document.addEventListener('click', () => {
            if (this.pendingUpdates.length > 0 && !this.notificationDisplayed) {
                this.showNotification();
            }
        });
    }

    /**
     * Check for updates via AJAX
     */
    checkForUpdates() {
        const url = '/system-updates/refresh';
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                localStorage.setItem('lastUpdateCheck', Date.now().toString());
                
                if (data.success && data.hasUpdates) {
                    this.pendingUpdates = data.updates || [];
                    
                    // Show notification if user is active
                    if (!this.notificationDisplayed && document.hasFocus()) {
                        this.showNotification();
                    }
                }
            })
            .catch(error => {
                console.error('Error checking for updates:', error);
            });
    }

    /**
     * Show an update notification to the user
     */
    showNotification() {
        if (this.pendingUpdates.length === 0) return;
        
        // Don't show again in this session
        this.notificationDisplayed = true;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'system-update-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-header">
                    <h5>System Update Available</h5>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="notification-body">
                    <p>A new system update (${this.pendingUpdates[0].version}) is available for your clinic.</p>
                    <p>View updates to apply or dismiss them.</p>
                </div>
                <div class="notification-footer">
                    <a href="/system-updates" class="view-btn">View Updates</a>
                    <button class="dismiss-btn">Dismiss</button>
                </div>
            </div>
        `;
        
        // Add notification styles
        const style = document.createElement('style');
        style.textContent = `
            .system-update-notification {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: white;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                border-radius: 8px;
                width: 320px;
                z-index: 9999;
                overflow: hidden;
                animation: slideIn 0.3s ease-out;
            }
            
            .notification-content {
                padding: 15px;
            }
            
            .notification-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .notification-header h5 {
                margin: 0;
                font-size: 16px;
                font-weight: bold;
            }
            
            .close-btn {
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
            
            .notification-body {
                margin-bottom: 15px;
            }
            
            .notification-body p {
                margin: 5px 0;
                font-size: 14px;
            }
            
            .notification-footer {
                display: flex;
                justify-content: space-between;
            }
            
            .view-btn {
                background: #3f51b5;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 14px;
                cursor: pointer;
                text-decoration: none;
            }
            
            .dismiss-btn {
                background: none;
                border: 1px solid #ddd;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 14px;
                cursor: pointer;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        
        // Add to document
        document.head.appendChild(style);
        document.body.appendChild(notification);
        
        // Add event listeners
        notification.querySelector('.close-btn').addEventListener('click', () => {
            notification.remove();
        });
        
        notification.querySelector('.dismiss-btn').addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize update checker when document is ready
document.addEventListener('DOMContentLoaded', () => {
    const updateChecker = new UpdateChecker();
    updateChecker.init();
}); 