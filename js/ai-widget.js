/**
 * E-Graphisme AI Dashboard Widget
 * Interactive AI Chat Interface
 */

const AIWidget = {
    // Container element
    container: null,
    
    // State
    isOpen: false,
    isMinimized: false,
    
    // Configuration
    config: {
        position: 'bottom-right',
        theme: 'dark',
        accentColor: '#7B2FFF',
        title: 'E-Graphisme AI',
        subtitle: 'Assistant virtuel',
        company: 'E-Graphisme By ELECTRON'
    },

    /**
     * Initialize the widget
     */
    initialize(options = {}) {
        this.config = { ...this.config, ...options };
        this.createContainer();
        this.bindEvents();
        return this;
    },

    /**
     * Create widget container
     */
    createContainer() {
        // Remove existing if any
        if (this.container) {
            this.container.remove();
        }

        // Create main container
        this.container = document.createElement('div');
        this.container.id = 'ai-widget-container';
        this.container.className = `ai-widget ${this.config.position}`;
        this.container.innerHTML = this.getWidgetHTML();
        
        document.body.appendChild(this.container);
        
        // Store references
        this.widget = this.container.querySelector('.ai-widget');
        this.toggleBtn = this.container.querySelector('.ai-toggle');
        this.chatWindow = this.container.querySelector('.ai-chat-window');
        this.messagesContainer = this.container.querySelector('.ai-messages');
        this.inputContainer = this.container.querySelector('.ai-input-container');
        this.input = this.container.querySelector('.ai-input');
    },

    /**
     * Get widget HTML
     */
    getWidgetHTML() {
        return `
        <style>
            .ai-widget {
                position: fixed;
                z-index: 10000;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            }
            .ai-widget.bottom-right {
                bottom: 90px;
                right: 20px;
            }
            .ai-widget.bottom-left {
                bottom: 90px;
                left: 20px;
            }
            .ai-widget * {
                box-sizing: border-box;
            }
            
            /* Toggle Button */
            .ai-toggle {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, ${this.config.accentColor}, #00D4FF);
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 20px rgba(123, 47, 255, 0.4);
                transition: all 0.3s ease;
            }
            .ai-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 30px rgba(123, 47, 255, 0.6);
            }
            .ai-toggle i {
                font-size: 24px;
                color: white;
            }
            .ai-toggle .ai-icon-close {
                display: none;
            }
            .ai-widget.open .ai-toggle .ai-icon-chat {
                display: none;
            }
            .ai-widget.open .ai-toggle .ai-icon-close {
                display: block;
            }
            
            /* Chat Window */
            .ai-chat-window {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 380px;
                height: 500px;
                max-height: 70vh;
                background: #1a1a2e;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
                display: none;
                flex-direction: column;
                overflow: hidden;
                border: 1px solid rgba(123, 47, 255, 0.3);
            }
            .ai-widget.open .ai-chat-window {
                display: flex;
            }
            .ai-widget.minimized .ai-chat-window {
                display: none;
            }
            
            /* Header */
            .ai-header {
                padding: 15px 20px;
                background: linear-gradient(135deg, ${this.config.accentColor}, #00D4FF);
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .ai-header-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: white;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .ai-header-avatar i {
                font-size: 20px;
                color: ${this.config.accentColor};
            }
            .ai-header-info {
                flex: 1;
            }
            .ai-header-title {
                font-size: 14px;
                font-weight: 600;
                color: white;
            }
            .ai-header-subtitle {
                font-size: 11px;
                color: rgba(255, 255, 255, 0.8);
            }
            .ai-header-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                padding: 5px;
                opacity: 0.8;
            }
            .ai-header-close:hover {
                opacity: 1;
            }
            
            /* Messages */
            .ai-messages {
                flex: 1;
                overflow-y: auto;
                padding: 15px;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .ai-message {
                max-width: 85%;
                padding: 12px 16px;
                border-radius: 18px;
                font-size: 13px;
                line-height: 1.5;
                animation: aiFadeIn 0.3s ease;
            }
            @keyframes aiFadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .ai-message-user {
                align-self: flex-end;
                background: linear-gradient(135deg, ${this.config.accentColor}, #00D4FF);
                color: white;
                border-bottom-right-radius: 4px;
            }
            .ai-message-ai {
                align-self: flex-start;
                background: #2a2a3e;
                color: white;
                border-bottom-left-radius: 4px;
            }
            .ai-message-typing {
                display: flex;
                gap: 4px;
            }
            .ai-message-typing span {
                width: 8px;
                height: 8px;
                background: rgba(123, 47, 255, 0.6);
                border-radius: 50%;
                animation: aiTyping 1.4s infinite;
            }
            .ai-message-typing span:nth-child(2) { animation-delay: 0.2s; }
            .ai-message-typing span:nth-child(3) { animation-delay: 0.4s; }
            @keyframes aiTyping {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-5px); }
            }
            
            /* Input */
            .ai-input-container {
                padding: 15px;
                background: #2a2a3e;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
            .ai-input-wrapper {
                display: flex;
                gap: 10px;
                background: #1a1a2e;
                border-radius: 25px;
                padding: 8px 15px;
                border: 1px solid rgba(123, 47, 255, 0.3);
            }
            .ai-input {
                flex: 1;
                background: none;
                border: none;
                color: white;
                font-size: 13px;
                outline: none;
            }
            .ai-input::placeholder {
                color: rgba(255, 255, 255, 0.5);
            }
            .ai-send-btn {
                background: linear-gradient(135deg, ${this.config.accentColor}, #00D4FF);
                border: none;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.2s;
            }
            .ai-send-btn:hover {
                transform: scale(1.1);
            }
            
            /* Quick Actions */
            .ai-quick-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                padding: 0 15px 10px;
            }
            .ai-quick-btn {
                padding: 6px 12px;
                background: rgba(123, 47, 255, 0.2);
                border: 1px solid rgba(123, 47, 255, 0.4);
                border-radius: 15px;
                color: white;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.2s;
            }
            .ai-quick-btn:hover {
                background: rgba(123, 47, 255, 0.4);
            }
            
            /* Suggestions */
            .ai-suggestions {
                display: none;
                padding: 10px 15px;
                background: #252535;
            }
            .ai-suggestions-title {
                font-size: 11px;
                color: rgba(255, 255, 255, 0.6);
                margin-bottom: 8px;
            }
            .ai-suggestions-list {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .ai-suggestion {
                padding: 8px 12px;
                background: rgba(0, 212, 255, 0.1);
                border-left: 3px solid #00D4FF;
                border-radius: 0 8px 8px 0;
                font-size: 12px;
                color: white;
                cursor: pointer;
                transition: all 0.2s;
            }
            .ai-suggestion:hover {
                background: rgba(0, 212, 255, 0.2);
            }
            
            @media (max-width: 480px) {
                .ai-widget.bottom-right {
                    right: 10px;
                    bottom: 80px;
                }
                .ai-chat-window {
                    width: calc(100vw - 30px);
                    right: -15px;
                }
            }
        </style>
        
        <button class="ai-toggle" title="${this.config.title}">
            <i class="fas fa-robot ai-icon-chat"></i>
            <i class="fas fa-times ai-icon-close"></i>
        </button>
        
        <div class="ai-chat-window">
            <div class="ai-header">
                <div class="ai-header-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="ai-header-info">
                    <div class="ai-header-title">${this.config.title}</div>
                    <div class="ai-header-subtitle">${this.config.subtitle}</div>
                </div>
                <button class="ai-header-close" title="Fermer">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            
            <div class="ai-messages">
                <div class="ai-message ai-message-ai">
                    ${this.getGreeting()}
                </div>
            </div>
            
            <div class="ai-quick-actions">
                <button class="ai-quick-btn" data-action="logo">Créer un logo</button>
                <button class="ai-quick-btn" data-action="site">Site web</button>
                <button class="ai-quick-btn" data-action="video">Vidéo IA</button>
                <button class="ai-quick-btn" data-action="seo">SEO</button>
            </div>
            
            <div class="ai-input-container">
                <div class="ai-input-wrapper">
                    <input type="text" class="ai-input" placeholder="Décrivez votre projet..." autocomplete="off">
                    <button class="ai-send-btn" title="Envoyer">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
        `;
    },

    /**
     * Get greeting message
     */
    getGreeting() {
        const greetings = [
            `Bonjour ! 👋 Je suis l'IA de ${this.config.company}.

Je peux vous aider avec :
🎨 Design graphique
🌐 Création de sites web
🎬 Production vidéo IA
📱 Réseaux sociaux
📝 Content marketing

Comment puis-je vous aider aujourd'hui ?`,
            `Salut ! Je suis votre assistant créatif IA. Décrivez-moi votre projet et je vous conseillera !`,
            `Bienvenue ! 💡 Je suis l'IA de E-Graphisme. Besoin d'aide pour votre branding, site web ou vidéos ?`
        ];
        return greetings[Math.floor(Math.random() * greetings.length)];
    },

    /**
     * Get suggestions based on context
     */
    getSuggestions() {
        return [
            'Créer un logo professionnel',
            'Refaire mon site web',
            'Vidéo marketing',
            'Audit SEO gratuit',
            'Devis pour e-commerce',
            'Identité visuelle complète'
        ];
    },

    /**
     * Bind events
     */
    bindEvents() {
        // Toggle chat
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.ai-toggle')) {
                this.toggle();
            } else if (e.target.closest('.ai-header-close')) {
                this.minimize();
            } else if (e.target.closest('.ai-quick-btn')) {
                const action = e.target.dataset.action;
                this.handleQuickAction(action);
            } else if (e.target.closest('.ai-send-btn') || (e.key === 'Enter' && e.target.closest('.ai-input'))) {
                this.sendMessage();
            }
        });

        // Input enter key
        this.input?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // ESC to close
        document.addEventListener('keypress', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    },

    /**
     * Toggle widget
     */
    toggle() {
        this.isOpen = !this.isOpen;
        this.widget?.classList.toggle('open', this.isOpen);
        
        if (this.isOpen) {
            this.input?.focus();
        }
    },

    /**
     * Minimize widget
     */
    minimize() {
        this.isMinimized = true;
        this.widget?.classList.add('minimized');
        setTimeout(() => {
            this.isMinimized = false;
            this.isOpen = false;
            this.widget?.classList.remove('open', 'minimized');
        }, 100);
    },

    /**
     * Close widget
     */
    close() {
        this.isOpen = false;
        this.widget?.classList.remove('open');
    },

    /**
     * Send message
     */
    async sendMessage() {
        const message = this.input?.value?.trim();
        if (!message) return;

        // Add user message
        this.addMessage(message, 'user');
        this.input.value = '';

        // Show typing indicator
        this.showTyping();

        try {
            // Process with AI Engine
            if (window.EGraphismeAI) {
                const response = await EGraphismeAI.process(message);
                this.removeTyping();
                this.addMessage(response.text || response.message || 'Merci pour votre message !', 'ai');
            } else {
                // Fallback response
                this.removeTyping();
                this.addMessage(this.getFallbackResponse(message), 'ai');
            }
        } catch (error) {
            this.removeTyping();
            this.addMessage('Désolé, une erreur est survenue. Veuillez réessayer.', 'ai');
        }
    },

    /**
     * Handle quick action
     */
    handleQuickAction(action) {
        const messages = {
            logo: 'Je souhaite créer un logo professionnel pour mon entreprise',
            site: 'Je voudrais un devis pour création de site web',
            video: 'Information sur la production vidéo IA',
            seo: 'Audit SEO gratuit pour mon site'
        };
        
        if (messages[action]) {
            this.input.value = messages[action];
            this.sendMessage();
        }
    },

    /**
     * Add message to chat
     */
    addMessage(text, sender = 'user') {
        const messageEl = document.createElement('div');
        messageEl.className = `ai-message ai-message-${sender}`;
        messageEl.textContent = text;
        this.messagesContainer?.appendChild(messageEl);
        this.scrollToBottom();
    },

    /**
     * Show typing indicator
     */
    showTyping() {
        const typingEl = document.createElement('div');
        typingEl.className = 'ai-message ai-message-typing';
        typingEl.innerHTML = '<span></span><span></span><span></span>';
        this.messagesContainer?.appendChild(typingEl);
        this.scrollToBottom();
    },

    /**
     * Remove typing indicator
     */
    removeTyping() {
        const typing = this.messagesContainer?.querySelector('.ai-message-typing');
        typing?.remove();
    },

    /**
     * Scroll to bottom
     */
    scrollToBottom() {
        this.messagesContainer?.scrollTo({
            top: this.messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    },

    /**
     * Get fallback response
     */
    getFallbackResponse(message) {
        const lower = message.toLowerCase();
        
        if (lower.includes('prix') || lower.includes('devis') || lower.includes('cout')) {
            return `Pour un devis précis, j'ai besoin de plus de détails :
- Type de projet (logo, site, vidéo...)
- Niveau de complexité
- Délai souhaité

Je peux vous appeler pour discuter ou收到 un email.`;
        }
        
        if (lower.includes('contact') || lower.includes('appeler')) {
            return `Bien sûr ! Vous pouvez nous contacter au :
📞 +229 01 977 003
📧 contact@e-graphisme.com
📍 Cotonou, {Bénin

Nous répondons sous 24h !`;
        }
        
        if (lower.includes('delai') || lower.includes('temps')) {
            return `Nos délais habituels :
- Logo : 5-7 jours
- Site vitrine : 2-3 semaines
- Site e-commerce : 4-6 semaines
- Vidéo IA : 3-7 jours

Rapide possible avec supplément.`;
        }
        
        return `Merci pour votre intérêt ! Pour vous répondre au mieux, précisez :
- Votre type de projet
- Votre budget approximatif
- Votre délai

Je prepare un devis personnalisé !`;
    },

    /**
     * Open widget programmatically
     */
    open() {
        this.isOpen = true;
        this.widget?.classList.add('open');
        setTimeout(() => this.input?.focus(), 300);
    },

    /**
     * Close widget programmatically
     */
    closeWidget() {
        this.isOpen = false;
        this.widget?.classList.remove('open');
    },

    /**
     * Destroy widget
     */
    destroy() {
        this.container?.remove();
        this.container = null;
    },

    /**
     * Version
     */
    version: '1.0.0'
};

// Auto-initialize when DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AIWidget.initialize());
} else {
    AIWidget.initialize();
}

// Export
window.AIWidget = AIWidget;