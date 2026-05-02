/* Toast Notification System - E-Graphisme */
(function() {
    'use strict';
    
    // Toast configuration
    const toastConfig = {
        duration: 4000,
        position: 'top-right',
        maxToasts: 5
    };
    
    // Toast types with colors
    const toastTypes = {
        success: { bg: '#10b981', icon: 'fa-check-circle', color: '#fff' },
        error: { bg: '#ef4444', icon: 'fa-exclamation-circle', color: '#fff' },
        warning: { bg: '#f59e0b', icon: 'fa-exclamation-triangle', color: '#fff' },
        info: { bg: '#6366f1', icon: 'fa-info-circle', color: '#fff' }
    };
    
    // Create toast container
    function createContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.innerHTML = `
                <style>
                    .toast-container {
                        position: fixed;
                        top: 100px;
                        right: 20px;
                        z-index: 99999;
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                        max-width: 380px;
                    }
                    .toast {
                        padding: 16px 20px;
                        border-radius: 12px;
                        color: white;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                        animation: slideInRight 0.3s ease;
                        cursor: pointer;
                        transition: transform 0.2s, opacity 0.2s;
                    }
                    .toast:hover {
                        transform: translateX(-5px);
                    }
                    .toast.removing {
                        animation: slideOutRight 0.3s ease forwards;
                    }
                    .toast-icon {
                        font-size: 1.3rem;
                        flex-shrink: 0;
                    }
                    .toast-content {
                        flex: 1;
                    }
                    .toast-title {
                        font-weight: 600;
                        font-size: 0.95rem;
                        margin-bottom: 2px;
                    }
                    .toast-message {
                        font-size: 0.85rem;
                        opacity: 0.9;
                    }
                    .toast-close {
                        background: none;
                        border: none;
                        color: white;
                        opacity: 0.7;
                        cursor: pointer;
                        padding: 5px;
                        font-size: 1rem;
                        transition: opacity 0.2s;
                    }
                    .toast-close:hover {
                        opacity: 1;
                    }
                    .toast-progress {
                        position: absolute;
                        bottom: 0;
                        left: 0;
                        height: 4px;
                        background: rgba(255,255,255,0.5);
                        border-radius: 0 0 12px 12px;
                        animation: progressShrink linear forwards;
                    }
                    @keyframes slideInRight {
                        from { opacity: 0; transform: translateX(100px); }
                        to { opacity: 1; transform: translateX(0); }
                    }
                    @keyframes slideOutRight {
                        from { opacity: 1; transform: translateX(0); }
                        to { opacity: 0; transform: translateX(100px); }
                    }
                    @keyframes progressShrink {
                        from { width: 100%; }
                        to { width: 0%; }
                    }
                    @media (max-width: 480px) {
                        .toast-container {
                            left: 10px;
                            right: 10px;
                            max-width: none;
                        }
                    }
                </style>
            `;
            document.body.appendChild(container);
        }
        return container;
    }
    
    // Create and show toast
    window.showToast = function(options) {
        const container = createContainer();
        
        // Limit number of toasts
        const existingToasts = container.querySelectorAll('.toast:not(.removing)');
        if (existingToasts.length >= toastConfig.maxToasts) {
            existingToasts[0].classList.add('removing');
            setTimeout(() => existingToasts[0].remove(), 300);
        }
        
        const { type = 'info', title = '', message = '', duration = toastConfig.duration } = options;
        const typeConfig = toastTypes[type] || toastTypes.info;
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.style.background = typeConfig.bg;
        toast.style.position = 'relative';
        toast.style.overflow = 'hidden';
        
        toast.innerHTML = `
            <i class="fas ${typeConfig.icon} toast-icon"></i>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
            <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
        `;
        
        // Add to container
        container.appendChild(toast);
        
        // Close button handler
        toast.querySelector('.toast-close').addEventListener('click', () => removeToast(toast));
        
        // Click to dismiss
        toast.addEventListener('click', () => removeToast(toast));
        
        // Auto remove after duration
        setTimeout(() => removeToast(toast), duration);
        
        return toast;
    };
    
    // Remove toast with animation
    function removeToast(toast) {
        if (toast.classList.contains('removing')) return;
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }
    
    // Convenience methods
    window.toast = {
        success: (title, msg) => showToast({ type: 'success', title, message: msg }),
        error: (title, msg) => showToast({ type: 'error', title, message: msg }),
        warning: (title, msg) => showToast({ type: 'warning', title, message: msg }),
        info: (title, msg) => showToast({ type: 'info', title, message: msg })
    };
    
})();
