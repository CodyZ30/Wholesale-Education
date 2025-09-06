// Global Notification System for Admin Panel
(function() {
    'use strict';
    
    class NotificationSystem {
        constructor() {
            this.notifications = [];
            this.soundEnabled = true;
            this.init();
        }
        
        init() {
            // Create notification container
            this.createContainer();
            
            // Initialize notification sound
            this.initSound();
            
            // Start checking for new notifications
            this.startPolling();
        }
        
        createContainer() {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
        
        initSound() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                this.playSound = () => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                };
            } catch (e) {
                console.log('Audio context not supported');
                this.playSound = () => {};
            }
        }
        
        show(title, message, type = 'info', options = {}) {
            const notification = {
                id: Date.now() + Math.random(),
                title,
                message,
                type,
                timestamp: new Date(),
                ...options
            };
            
            this.notifications.push(notification);
            this.renderNotification(notification);
            
            if (this.soundEnabled) {
                this.playSound();
            }
            
            return notification.id;
        }
        
        renderNotification(notification) {
            const container = document.getElementById('notification-container');
            const notificationEl = document.createElement('div');
            notificationEl.className = `notification bg-white border-l-4 shadow-lg rounded-lg p-4 max-w-sm transform transition-all duration-300 ease-in-out ${this.getTypeClass(notification.type)}`;
            notificationEl.style.transform = 'translateX(100%)';
            notificationEl.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="${this.getTypeIcon(notification.type)} text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${this.escapeHtml(notification.title)}</h4>
                        <p class="text-sm text-gray-600 mt-1">${this.escapeHtml(notification.message)}</p>
                        <div class="text-xs text-gray-500 mt-2">
                            ${notification.timestamp.toLocaleTimeString()}
                        </div>
                        ${notification.actions ? `
                            <div class="flex gap-2 mt-3">
                                ${notification.actions.map(action => `
                                    <button onclick="${action.onclick}" class="px-3 py-1 text-xs rounded font-medium ${action.class || 'bg-blue-600 text-white hover:bg-blue-700'}">
                                        ${action.text}
                                    </button>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                    <button onclick="window.notificationSystem.dismiss('${notification.id}')" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(notificationEl);
            
            // Animate in
            setTimeout(() => {
                notificationEl.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto-dismiss after delay
            const delay = notification.delay || 5000;
            setTimeout(() => {
                this.dismiss(notification.id);
            }, delay);
        }
        
        dismiss(id) {
            const notificationEl = document.querySelector(`[data-notification-id="${id}"]`);
            if (notificationEl) {
                notificationEl.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notificationEl.parentElement) {
                        notificationEl.remove();
                    }
                }, 300);
            }
            
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
        
        getTypeClass(type) {
            switch (type) {
                case 'success': return 'border-green-500';
                case 'error': return 'border-red-500';
                case 'warning': return 'border-yellow-500';
                case 'info': return 'border-blue-500';
                default: return 'border-gray-500';
            }
        }
        
        getTypeIcon(type) {
            switch (type) {
                case 'success': return 'fas fa-check-circle text-green-500';
                case 'error': return 'fas fa-exclamation-circle text-red-500';
                case 'warning': return 'fas fa-exclamation-triangle text-yellow-500';
                case 'info': return 'fas fa-info-circle text-blue-500';
                default: return 'fas fa-bell text-gray-500';
            }
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        startPolling() {
            // Check for new chat messages every 5 seconds
            setInterval(() => {
                this.checkForNewChatMessages();
            }, 5000);
            
            // Check for new support tickets every 10 seconds
            setInterval(() => {
                this.checkForNewSupportTickets();
            }, 10000);
        }
        
        async checkForNewChatMessages() {
            try {
                const response = await fetch('/api/chat/notifications');
                const data = await response.json();
                
                if (data.success && data.new_messages) {
                    data.new_messages.forEach(msg => {
                        this.show(
                            'New Chat Message',
                            `${msg.visitor_name}: ${msg.message.substring(0, 50)}...`,
                            'info',
                            {
                                actions: [{
                                    text: 'Open Chat',
                                    onclick: `window.open('/admin/live_chat.php', '_blank')`,
                                    class: 'bg-blue-600 text-white hover:bg-blue-700'
                                }],
                                delay: 8000
                            }
                        );
                    });
                }
            } catch (error) {
                console.error('Failed to check for new chat messages:', error);
            }
        }
        
        async checkForNewSupportTickets() {
            try {
                const response = await fetch('/api/support/notifications');
                const data = await response.json();
                
                if (data.success && data.new_tickets) {
                    data.new_tickets.forEach(ticket => {
                        this.show(
                            'New Support Ticket',
                            `${ticket.subject} - ${ticket.priority} priority`,
                            ticket.priority === 'urgent' ? 'error' : 'info',
                            {
                                actions: [{
                                    text: 'View Ticket',
                                    onclick: `window.open('/admin/support.php', '_blank')`,
                                    class: 'bg-blue-600 text-white hover:bg-blue-700'
                                }],
                                delay: 8000
                            }
                        );
                    });
                }
            } catch (error) {
                console.error('Failed to check for new support tickets:', error);
            }
        }
        
        // Public methods
        success(title, message, options = {}) {
            return this.show(title, message, 'success', options);
        }
        
        error(title, message, options = {}) {
            return this.show(title, message, 'error', options);
        }
        
        warning(title, message, options = {}) {
            return this.show(title, message, 'warning', options);
        }
        
        info(title, message, options = {}) {
            return this.show(title, message, 'info', options);
        }
        
        chatMessage(visitorName, message, chatId) {
            return this.show(
                `New message from ${visitorName}`,
                message.substring(0, 100) + (message.length > 100 ? '...' : ''),
                'info',
                {
                    actions: [{
                        text: 'Open Chat',
                        onclick: `window.open('/admin/live_chat.php', '_blank')`,
                        class: 'bg-blue-600 text-white hover:bg-blue-700'
                    }],
                    delay: 10000
                }
            );
        }
        
        supportTicket(subject, priority, ticketId) {
            return this.show(
                'New Support Ticket',
                `${subject} - ${priority} priority`,
                priority === 'urgent' ? 'error' : 'info',
                {
                    actions: [{
                        text: 'View Ticket',
                        onclick: `window.open('/admin/support.php', '_blank')`,
                        class: 'bg-blue-600 text-white hover:bg-blue-700'
                    }],
                    delay: 10000
                }
            );
        }
    }
    
    // Initialize global notification system
    window.notificationSystem = new NotificationSystem();
    
    // Add CSS for notifications
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            animation: slideIn 0.3s ease-out;
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
        
        .notification:hover {
            transform: translateX(-5px);
        }
    `;
    document.head.appendChild(style);
    
})();
