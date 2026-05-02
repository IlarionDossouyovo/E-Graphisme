// Live Chat Widget - E-Graphisme
// Support WhatsApp et formulaire de chat intégré

(function() {
    'use strict';

    // Configuration - À personnaliser
    const config = {
        whatsappNumber: '+33123456789', // Votre numéro WhatsApp
        companyName: 'E-Graphisme',
        position: 'bottom-right', // bottom-right, bottom-left
        primaryColor: '#6366f1',
        availabilityHours: {
            start: 9, // 9h
            end: 18   // 18h
        }
    };

    // Vérifier si c'est les heures de bureau
    function isDuringBusinessHours() {
        const now = new Date();
        const hour = now.getHours();
        const day = now.getDay();
        
        // Vérifier si c'est un jour ouvré (lundi-vendredi)
        if (day === 0 || day === 6) return false;
        
        return hour >= config.availabilityHours.start && hour < config.availabilityHours.end;
    }

    // Créer le widget de chat
    function createChatWidget() {
        // Supprimer si déjà existant
        const existingWidget = document.getElementById('egraphisme-chat-widget');
        if (existingWidget) return;

        // Créer le conteneur principal
        const widget = document.createElement('div');
        widget.id = 'egraphisme-chat-widget';
        widget.innerHTML = `
            <!-- Bouton d'ouverture -->
            <div class="chat-toggle" id="chatToggle">
                <i class="fas fa-comments"></i>
                <span class="chat-notification" id="chatNotification" style="display: none;">1</span>
            </div>

            <!-- Fenêtre de chat -->
            <div class="chat-window" id="chatWindow">
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-status ${isDuringBusinessHours() ? 'online' : 'offline'}"></div>
                        <div>
                            <h4>${config.companyName}</h4>
                            <p>${isDuringBusinessHours() ? 'En ligne' : 'Hors ligne'}</p>
                        </div>
                    </div>
                    <button class="chat-close" id="chatClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-message bot">
                        <p>Bonjour ! 👋</p>
                        <p>Comment puis-je vous aider aujourd'hui ?</p>
                        <span class="chat-time">${new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
                    </div>
                </div>
                
                <div class="chat-quick-actions">
                    <button class="quick-action" data-action="whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </button>
                    <button class="quick-action" data-action="callback">
                        <i class="fas fa-phone"></i> Rappeler
                    </button>
                    <button class="quick-action" data-action="email">
                        <i class="fas fa-envelope"></i> Email
                    </button>
                </div>
                
                <form class="chat-form" id="chatForm">
                    <input type="text" id="chatInput" placeholder="Tapez votre message..." autocomplete="off">
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        `;

        // Ajouter les styles
        const styles = document.createElement('style');
        styles.textContent = `
            #egraphisme-chat-widget {
                position: fixed;
                ${config.position.split('-')[0]}: 30px;
                ${config.position.split('-')[1]}: 30px;
                z-index: 10000;
                font-family: 'Poppins', sans-serif;
            }

            .chat-toggle {
                width: 60px;
                height: 60px;
                background: ${config.primaryColor};
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 5px 20px rgba(99, 102, 241, 0.4);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                position: relative;
            }

            .chat-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
            }

            .chat-toggle i {
                color: white;
                font-size: 1.5rem;
            }

            .chat-notification {
                position: absolute;
                top: -5px;
                right: -5px;
                width: 22px;
                height: 22px;
                background: #ef4444;
                border-radius: 50%;
                color: white;
                font-size: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            }

            .chat-window {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 380px;
                max-width: calc(100vw - 60px);
                height: 500px;
                max-height: calc(100vh - 120px);
                background: white;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                display: none;
                flex-direction: column;
                overflow: hidden;
                animation: slideUp 0.3s ease;
            }

            .chat-window.active {
                display: flex;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .chat-header {
                background: linear-gradient(135deg, ${config.primaryColor} 0%, #f472b6 100%);
                color: white;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .chat-header-info {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .chat-status {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                border: 2px solid white;
            }

            .chat-status.online {
                background: #10b981;
            }

            .chat-status.offline {
                background: #ef4444;
            }

            .chat-header-info h4 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }

            .chat-header-info p {
                margin: 0;
                font-size: 0.8rem;
                opacity: 0.9;
            }

            .chat-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                padding: 5px;
                opacity: 0.8;
                transition: opacity 0.3s;
            }

            .chat-close:hover {
                opacity: 1;
            }

            .chat-messages {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
                background: #f8fafc;
            }

            .chat-message {
                margin-bottom: 15px;
                max-width: 80%;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            .chat-message.bot {
                align-self: flex-start;
            }

            .chat-message.user {
                margin-left: auto;
                text-align: right;
            }

            .chat-message p {
                margin: 0 0 5px;
                padding: 12px 16px;
                border-radius: 15px;
                font-size: 0.9rem;
                line-height: 1.5;
            }

            .chat-message.bot p {
                background: white;
                color: #1e1e2f;
                border-bottom-left-radius: 5px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }

            .chat-message.user p {
                background: ${config.primaryColor};
                color: white;
                border-bottom-right-radius: 5px;
            }

            .chat-time {
                font-size: 0.7rem;
                color: #94a3b8;
                display: block;
                margin-top: 5px;
            }

            .chat-quick-actions {
                display: flex;
                gap: 10px;
                padding: 15px;
                background: white;
                border-top: 1px solid #e2e8f0;
                flex-wrap: wrap;
            }

            .quick-action {
                flex: 1;
                min-width: 90px;
                padding: 10px;
                border: 2px solid ${config.primaryColor};
                background: transparent;
                color: ${config.primaryColor};
                border-radius: 10px;
                font-size: 0.8rem;
                font-weight: 500;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                transition: all 0.3s ease;
            }

            .quick-action:hover {
                background: ${config.primaryColor};
                color: white;
            }

            .chat-form {
                display: flex;
                gap: 10px;
                padding: 15px;
                background: white;
                border-top: 1px solid #e2e8f0;
            }

            .chat-form input {
                flex: 1;
                padding: 12px 16px;
                border: 2px solid #e2e8f0;
                border-radius: 25px;
                font-size: 0.9rem;
                outline: none;
                transition: border-color 0.3s;
            }

            .chat-form input:focus {
                border-color: ${config.primaryColor};
            }

            .chat-form button {
                width: 45px;
                height: 45px;
                background: ${config.primaryColor};
                border: none;
                border-radius: 50%;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.3s ease, background 0.3s ease;
            }

            .chat-form button:hover {
                transform: scale(1.1);
                background: #4f46e5;
            }

            /* Responsive */
            @media (max-width: 992px) {
                #egraphisme-chat-widget {
                    right: 20px;
                    bottom: 20px;
                }

                .chat-window {
                    width: 350px;
                }
            }
            
            @media (max-width: 768px) {
                #egraphisme-chat-widget {
                    right: 15px;
                    bottom: 15px;
                }

                .chat-window {
                    width: calc(100vw - 40px);
                    height: 60vh;
                    right: -5px;
                }
                
                .chat-quick-actions {
                    flex-wrap: wrap;
                }
                
                .quick-action {
                    min-width: 80px;
                    font-size: 0.75rem;
                    padding: 8px;
                }
            }
            
            @media (max-width: 480px) {
                #egraphisme-chat-widget {
                    right: 10px;
                    bottom: 10px;
                }

                .chat-toggle {
                    width: 50px;
                    height: 50px;
                }
                
                .chat-toggle i {
                    font-size: 1.2rem;
                }

                .chat-window {
                    width: calc(100vw - 20px);
                    height: calc(100vh - 80px);
                    right: -5px;
                    bottom: 70px;
                }
                
                .chat-header {
                    padding: 15px;
                }
                
                .chat-header-info h4 {
                    font-size: 0.9rem;
                }
                
                .chat-header-info p {
                    font-size: 0.75rem;
                }
                
                .chat-messages {
                    padding: 15px;
                }
                
                .chat-message p {
                    padding: 10px 12px;
                    font-size: 0.85rem;
                }
                
                .chat-quick-actions {
                    padding: 10px;
                    gap: 8px;
                }
                
                .quick-action {
                    min-width: 70px;
                    padding: 8px 5px;
                    font-size: 0.7rem;
                }
                
                .quick-action i {
                    display: none;
                }
                
                .chat-form {
                    padding: 10px;
                    gap: 8px;
                }
                
                .chat-form input {
                    padding: 10px 14px;
                    font-size: 0.85rem;
                }
                
                .chat-form button {
                    width: 40px;
                    height: 40px;
                }
            }
        `;

        document.head.appendChild(styles);
        document.body.appendChild(widget);

        // Ajouter les écouteurs d'événements
        setupEventListeners();
    }

    // Configurer les écouteurs d'événements
    function setupEventListeners() {
        const toggle = document.getElementById('chatToggle');
        const chatWindow = document.getElementById('chatWindow');
        const chatClose = document.getElementById('chatClose');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatMessages = document.getElementById('chatMessages');

        // Ouvrir/fermer le chat
        toggle.addEventListener('click', () => {
            chatWindow.classList.toggle('active');
            if (chatWindow.classList.contains('active')) {
                chatInput.focus();
            }
        });

        chatClose.addEventListener('click', () => {
            chatWindow.classList.remove('active');
        });

        // Envoyer un message
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            
            if (message) {
                addMessage(message, 'user');
                chatInput.value = '';

                // Simulation de réponse
                setTimeout(() => {
                    const responses = [
                        "Merci pour votre message ! Un de nos conseillers va vous répondre sous peu.",
                        "Intéressant ! Pouvez-vous nous donner plus de détails sur votre projet ?",
                        "Nous avons bien reçu votre demande. Notre équipe vous contactera rapidement.",
                        "Parfait ! Découvrez nos services sur notre site."
                    ];
                    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                    addMessage(randomResponse, 'bot');
                }, 1000);
            }
        });

        // Actions rapides
        document.querySelectorAll('.quick-action').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                
                switch(action) {
                    case 'whatsapp':
                        window.open(`https://wa.me/${config.whatsappNumber.replace(/\D/g, '')}`, '_blank');
                        break;
                    case 'callback':
                        addMessage("Veuillez Leavez votre numéro et nous vous appellerons.", 'bot');
                        break;
                    case 'email':
                        window.location.href = 'mailto:contact@e-graphisme.com';
                        break;
                }
            });
        });
    }

    // Ajouter un message au chat
    function addMessage(text, sender) {
        const chatMessages = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        messageDiv.innerHTML = `
            <p>${text}</p>
            <span class="chat-time">${new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</span>
        `;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Initialiser le widget au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createChatWidget);
    } else {
        createChatWidget();
    }

})();
