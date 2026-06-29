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
('Direction', 'direction', 'Direction générale et stratégie', '#D4AF37', '👑'),
('Design Graphique', 'design', 'Conception visuelle et branding', '#00B8FF', '🎨'),
('Développement', 'development', 'Développement web et applications', '#7A3CFF', '💻'),
('Marketing', 'marketing', 'Marketing digital et communication', '#00F0FF', '📢'),
('Production Vidéo', 'production', 'Production vidéo et motion design', '#FFD54F', '🎬'),
('Intelligence Artificielle', 'ai', 'Recherche et développement IA', '#FF6B6B', '🤖'),
('Support Client', 'support', 'Support et relation client', '#4CAF50', '🎧');

-- Insérer les 26 agents IA
INSERT INTO ai_agents (name, slug, model_name, department_id, description, capabilities, instructions, color, status) VALUES
-- Direction (3 agents)
('ELECTRON CEO', 'ceo', 'llama3.2:latest', 1, 'Assistant exécutif principal - Stratégie et vision', '["Vision", "Stratégie", "Décision", "Leadership", "Relations"]', 'Vous êtes ELECTRON CEO, l\'assistant exécutif principal de l\'entreprise ELECTRON.', '#D4AF37', 'active'),
('ELECTRON CFO', 'cfo', 'llama3.1:8b', 1, 'Directeur financier - Finance et analyse', '["Finance", "Budget", "Analyse", "Investissement", "Rapports"]', 'Vous êtes ELECTRON CFO, le directeur financier.', '#D4AF37', 'active'),
('ELECTRON COO', 'coo', 'llama3.2:latest', 1, 'Directeur des opérations - Optimisation', '["Opérations", "Processus", "Efficacité", "Logistique", "QC"]', 'Vous êtes ELECTRON COO, le directeur des opérations.', '#D4AF37', 'active'),

-- Design (4 agents)
('ELECTRON Brand Architect', 'brand-architect', 'llama3.2:latest', 2, 'Architecte de marque - Identité visuelle', '["Brand Identity", "Logo", "Charte", "Guidelines", "Positionnement"]', 'Vous êtes ELECTRON Brand Architect, l\'expert en identité de marque.', '#00B8FF', 'active'),
('ELECTRON UI Master', 'ui-master', 'llama3.2:latest', 2, 'Designer UI - Interfaces utilisateur', '["UI Design", "Figma", "Prototypage", "Design System", "Animations"]', 'Vous êtes ELECTRON UI Master, le expert en interfaces.', '#00B8FF', 'active'),
('ELECTRON UX Researcher', 'ux-researcher', 'llama3.1:8b', 2, 'Chercheur UX - Expérience utilisateur', '["UX Research", "Tests", "Personas", "Parcours", "Accessibilité"]', 'Vous êtes ELECTRON UX Researcher, l\'expert en expérience utilisateur.', '#00B8FF', 'active'),
('ELECTRON Print Expert', 'print-expert', 'llama3.2:latest', 2, 'Expert impression - Print et packaging', '["Print", "Packaging", "Typographie", "Couleurs CMJN", "Production"]', 'Vous êtes ELECTRON Print Expert, l\'expert en impression.', '#00B8FF', 'active'),

-- Développement (4 agents)
('ELECTRON FullStack', 'fullstack', 'qwen2.5-coder:7b', 3, 'Développeur full-stack - Web et mobile', '["React", "Node.js", "PHP", "API", "Mobile"]', 'Vous êtes ELECTRON FullStack, le développeur complet.', '#7A3CFF', 'active'),
('ELECTRON Security', 'security', 'qwen2.5-coder:7b', 3, 'Expert sécurité - Cybersécurité', '["Sécurité", "Audit", "Cryptographie", "Pentest", "GDPR"]', 'Vous êtes ELECTRON Security, l\'expert en sécurité.', '#7A3CFF', 'active'),
('ELECTRON DevOps', 'devops', 'qwen2.5-coder:7b', 3, 'Ingénieur DevOps - Infrastructure', '["Docker", "CI/CD", "AWS", "Linux", "Monitoring"]', 'Vous êtes ELECTRON DevOps, l\'expert en infrastructure.', '#7A3CFF', 'active'),
('ELECTRON Database', 'database', 'qwen2.5-coder:7b', 3, 'Expert base de données', '["SQL", "PostgreSQL", "MongoDB", "Optimisation", "Migration"]', 'Vous êtes ELECTRON Database, l\'expert en bases de données.', '#7A3CFF', 'active'),

-- Marketing (4 agents)
('ELECTRON Growth Hacker', 'growth', 'llama3.1:8b', 4, 'Growth hacker - Acquisition', '["Growth", "Acquisition", "Conversion", "A/B Testing", "Funnels"]', 'Vous êtes ELECTRON Growth Hacker, l\'expert en croissance.', '#00F0FF', 'active'),
('ELECTRON Copywriter', 'copywriter', 'llama3.1:8b', 4, 'Rédacteur marketing - Contenu', '["Copywriting", "SEO Content", "Emails", "Ads", "Storytelling"]', 'Vous êtes ELECTRON Copywriter, le rédacteur expert.', '#00F0FF', 'active'),
('ELECTRON Social Manager', 'social', 'llama3.1:8b', 4, 'Manager réseaux sociaux', '["Social Media", "Instagram", "LinkedIn", "Twitter", "Community"]', 'Vous êtes ELECTRON Social Manager, le manager des réseaux sociaux.', '#00F0FF', 'active'),
('ELECTRON SEO Expert', 'seo', 'llama3.1:8b', 4, 'Expert SEO - Référencement', '["SEO", "Keywords", "Backlinks", "Technical SEO", "Local SEO"]', 'Vous êtes ELECTRON SEO Expert, l\'expert en référencement.', '#00F0FF', 'active'),

-- Production Vidéo (4 agents)
('ELECTRON Video Director', 'video-director', 'llama3.2:latest', 5, 'Réalisateur vidéo', '["Réalisation", "Script", "Storyboard", "Cinéma", "Direction"]', 'Vous êtes ELECTRON Video Director, le réalisateur.', '#FFD54F', 'active'),
('ELECTRON Motion Designer', 'motion', 'llama3.2:latest', 5, 'Motion designer - Animation', '["After Effects", "Animation", "Motion Graphics", "Transitions", "Logo Animation"]', 'Vous êtes ELECTRON Motion Designer, l\'expert en animation.', '#FFD54F', 'active'),
('ELECTRON Colorist', 'colorist', 'llama3.2:latest', 5, 'Étalonneur - Étalonnage couleur', '["DaVinci Resolve", "Color Grading", "Etalonnage", "LUTs", "Grading"]', 'Vous êtes ELECTRON Colorist, l\'étalonneur.', '#FFD54F', 'active'),
('ELECTRON Sound Designer', 'sound', 'llama3.2:latest', 5, 'Designer sonore - Audio', '["Sound Design", "Mixage", "Musique", "SFX", "Audio Pro"]', 'Vous êtes ELECTRON Sound Designer, l\'expert audio.', '#FFD54F', 'active'),

-- IA R&D (3 agents)
('ELECTRON Prompt Engineer', 'prompt-engineer', 'llama3.2:latest', 6, 'Ingénieur prompts - Optimisation', '["Prompt Engineering", "Chain of Thought", "Few-shot", "Roleplay", "Optimization"]', 'Vous êtes ELECTRON Prompt Engineer, l\'expert en prompts.', '#FF6B6B', 'active'),
('ELECTRON ML Engineer', 'ml-engineer', 'llama3.2:latest', 6, 'Ingénieur machine learning', '["Machine Learning", "TensorFlow", "PyTorch", "Training", "Fine-tuning"]', 'Vous êtes ELECTRON ML Engineer, l\'expert en machine learning.', '#FF6B6B', 'active'),
('ELECTRON Data Analyst', 'data-analyst', 'llama3.1:8b', 6, 'Analyste données', '["Data Analysis", "Visualisation", "Statistiques", "BI", "Rapports"]', 'Vous êtes ELECTRON Data Analyst, l\'expert en données.', '#FF6B6B', 'active'),

-- Support (4 agents)
('ELECTRON Account Manager', 'account', 'llama3.1:8b', 7, 'Gestionnaire de compte client', '["Account Management", "Relation Client", "Upselling", "Retention", "QBR"]', 'Vous êtes ELECTRON Account Manager, le gestionnaire de comptes.', '#4CAF50', 'active'),
('ELECTRON Success Manager', 'success', 'llama3.1:8b', 7, 'Manager succès client', '["Onboarding", "Formation", "Adoption", "NPS", "Evangelism"]', 'Vous êtes ELECTRON Success Manager, l\'expert en succès client.', '#4CAF50', 'active'),
('ELECTRON Sales Closer', 'sales', 'llama3.1:8b', 7, 'Commercial - Clôture', '["Vente", "Prospection", "Négociation", "Closing", "Pipeline"]', 'Vous êtes ELECTRON Sales Closer, le commercial expert.', '#4CAF50', 'active'),
('ELECTRON HR Bot', 'hr', 'llama3.1:8b', 7, 'RH - Gestion humaine', '["Recrutement", "Onboarding", "Formation", "Politiques", "Benefits"]', 'Vous êtes ELECTRON HR Bot, l\'expert RH.', '#4CAF50', 'active');

-- Insérer les services
INSERT INTO services (name, slug, department_id, description, price, features) VALUES
('Logo Professionnel', 'logo', 2, 'Création de logo professionnel avec unlimited révisions', 150.00, '["Logo vectoriel", "3 révisions", "Fichiers sources", "Charte graphique"]'),
('Identité Visuelle', 'identity', 2, 'Identité visuelle complète', 500.00, '["Logo", "Charte graphique", "Papeterie", "Guide d\'utilisation"]'),
('Site Web', 'website', 3, 'Développement site web responsive', 800.00, '["Design responsive", "SEO optimisé", "Admin panel", "Support 3 mois"]'),
('Application Mobile', 'app', 3, 'Application iOS/Android', 2500.00, '["iOS & Android", "API backend", "Notifications push", "Maintenance 1 an"]'),
('Vidéo Promotionnelle', 'video', 5, 'Vidéo promotionnelle 2-3 min', 400.00, '["Script", "Montage", "Musique", "Sous-titres"]'),
('Motion Design', 'motion', 5, 'Animation et motion design', 300.00, '["Animation logo", "Transitions", "Effects", "Export multi-format"]'),
('Consulting IA', 'consulting', 6, 'Conseil en intelligence artificielle', 200.00, '["Audit", "Recommandations", "Feuille de route", "Suivi"]');