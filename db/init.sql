-- =============================================
-- E-GRAPHISME & E-STUDIO by ELECTRON
-- Database Schema
-- =============================================

-- Utilisateurs / Utilisateurs de la plateforme
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('founder', 'admin', 'manager', 'designer', 'developer', 'client') DEFAULT 'client',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    avatar VARCHAR(255),
    phone VARCHAR(50),
    company VARCHAR(255),
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Departments / Départements de l'entreprise
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(20) DEFAULT '#D4AF37',
    icon VARCHAR(50),
    head_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_id) REFERENCES users(id) ON DELETE SET NULL
);

-- AI Agents / Agents IA de l'entreprise
CREATE TABLE IF NOT EXISTS ai_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    model_name VARCHAR(100) NOT NULL,
    department_id INT,
    description TEXT,
    capabilities TEXT, -- JSON array
    instructions TEXT,
    system_prompt TEXT,
    avatar VARCHAR(255),
    color VARCHAR(20) DEFAULT '#00B8FF',
    status ENUM('active', 'inactive', 'training') DEFAULT 'active',
    temperature FLOAT DEFAULT 0.7,
    max_tokens INT DEFAULT 4096,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Projects / Projets
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    department_id INT,
    user_id INT,
    status ENUM('draft', 'in_progress', 'review', 'completed', 'archived') DEFAULT 'draft',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    budget DECIMAL(15,2),
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Orders / Commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    project_id INT,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'EUR',
    status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

-- Services / Services proposés
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    department_id INT,
    price DECIMAL(15,2),
    currency VARCHAR(10) DEFAULT 'EUR',
    duration_days INT,
    features TEXT, -- JSON array
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Messages / Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agent_id INT,
    content TEXT NOT NULL,
    role ENUM('user', 'assistant', 'system') DEFAULT 'user',
    metadata TEXT, -- JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES ai_agents(id) ON DELETE SET NULL
);

-- API Keys / Clés API
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(100),
    permissions TEXT, -- JSON array
    expires_at DATE,
    last_used TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insérer l'utilisateur fondateur par défaut
INSERT INTO users (username, email, password_hash, role, status, company) 
VALUES ('founder', 'founder@electron.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'founder', 'active', 'ELECTRON');

-- Insérer les départements
INSERT INTO departments (name, slug, description, color, icon) VALUES
('Direction', 'direction', 'Direction générale et stratégie', '#D4AF37', 'fa-crown'),
('Design Graphique', 'design', 'Conception visuelle et branding', '#00B8FF', 'fa-palette'),
('Développement', 'development', 'Développement web et applications', '#7A3CFF', 'fa-code'),
('Marketing', 'marketing', 'Marketing digital et communication', '#00F0FF', 'fa-bullhorn'),
('Production Vidéo', 'production', 'Production vidéo et motion design', '#FFD54F', 'fa-video'),
('Intelligence Artificielle', 'ai', 'Recherche et développement IA', '#FF6B6B', 'fa-robot'),
('Support Client', 'support', 'Support et relation client', '#4CAF50', 'fa-headset');

-- Insérer les agents IA
INSERT INTO ai_agents (name, slug, model_name, department_id, description, capabilities, instructions, color, status) VALUES
-- Direction IA
('ELECTRON Director', 'director', 'llama3.2:latest', 1, 'Assistant de direction stratégique pour la prise de décision', '["stratégie", "analyse", "planification", "décision"]', 'Vous êtes ELECTRON Director, l\'assistant IA de direction de l\'entreprise ELECTRON. Vous aidez à la prise de décision stratégique, l\'analyse de marché et la planification à long terme.', '#D4AF37', 'active'),

-- Design IA
('ELECTRON Designer', 'designer', 'llama3.2:latest', 2, 'Assistant de conception graphique et design', '["création.logo", "charte.graphique", "design.ui", "branding", "conseil.créatif"]', 'Vous êtes ELECTRON Designer, l\'expert en design de l\'entreprise. Vous aidez à créer des identités visuelles, des logos, des chartes graphiques et des designs UI/UX.', '#00B8FF', 'active'),

-- Development IA
('ELECTRON Developer', 'developer', 'qwen2.5-coder:7b', 3, 'Assistant de développement et programmation', '["code", "debug", "architecture", "refactoring", "documentation"]', 'Vous êtes ELECTRON Developer, l\'expert en développement. Vous aidez à écrire du code, résoudre des bugs, créer des architectures et documenter les projets.', '#7A3CFF', 'active'),

-- Marketing IA
('ELECTRON Marketer', 'marketer', 'llama3.1:8b', 4, 'Assistant marketing et communication', '["seo", "content.marketing", "social.media", "email.marketing", "analytics"]', 'Vous êtes ELECTRON Marketer, l\'expert en marketing. Vous aidez à créer des stratégies marketing, du contenu, et analyser les performances.', '#00F0FF', 'active'),

-- Production Vidéo IA
('ELECTRON Producer', 'producer', 'llama3.2:latest', 5, 'Assistant de production vidéo', '["script", "storyboard", "montage", "motion.design", "effects"]', 'Vous êtes ELECTRON Producer, l\'expert en production vidéo. Vous aidez à créer des scripts, storyboards, plans de montage et effets visuels.', '#FFD54F', 'active'),

-- IA R&D
('ELECTRON AI', 'ai-research', 'llama3.2:latest', 6, 'Assistant de recherche IA', '["prompt.engineering", "fine.tuning", "model.training", "data.analysis"]', 'Vous êtes ELECTRON AI, l\'expert en intelligence artificielle. Vous aidez à créer des prompts, fine-tuner des modèles et analyser des données.', '#FF6B6B', 'active'),

-- Support IA
('ELECTRON Support', 'support', 'llama3.1:8b', 7, 'Assistant support client', '["support.technique", "faq", "troubleshooting", "relationship.client"]', 'Vous êtes ELECTRON Support, l\'expert en support client. Vous aidez à répondre aux questions, résoudre les problèmes et maintenir la relation client.', '#4CAF50', 'active');

-- Insérer les services
INSERT INTO services (name, slug, department_id, description, price, features) VALUES
('Logo Professionnel', 'logo', 2, 'Création de logo professionnel avec unlimited révisions', 150.00, '["Logo vectoriel", "3 révisions", "Fichiers sources", "Charte graphique"]'),
('Identité Visuelle', 'identity', 2, 'Identité visuelle complète', 500.00, '["Logo", "Charte graphique", "Papeterie", "Guide d\'utilisation"]'),
('Site Web', 'website', 3, 'Développement site web responsive', 800.00, '["Design responsive", "SEO optimisé", "Admin panel", "Support 3 mois"]'),
('Application Mobile', 'app', 3, 'Application iOS/Android', 2500.00, '["iOS & Android", "API backend", "Notifications push", "Maintenance 1 an"]'),
('Vidéo Promotionnelle', 'video', 5, 'Vidéo promotionnelle 2-3 min', 400.00, '["Script", "Montage", "Musique", "Sous-titres"]'),
('Motion Design', 'motion', 5, 'Animation et motion design', 300.00, '["Animation logo", "Transitions", "Effects", "Export multi-format"]'),
('Consulting IA', 'consulting', 6, 'Conseil en intelligence artificielle', 200.00, '["Audit", "Recommandations", "Feuille de route", "Suivi"]');