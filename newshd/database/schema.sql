-- NEWSHD News Portal Database Schema
-- MySQL 8+ / MariaDB 10.4+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS news_portal
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE news_portal;

-- Roles
CREATE TABLE roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT-- NULL,
  slug VARCHAR(50) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_roles_slug (slug)
) ENGINE=InnoDB;

-- Users
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id TINYINT UNSIGNED NOT NULL DEFAULT 4,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  email_verified_at DATETIME DEFAULT NULL,
  verification_token VARCHAR(64) DEFAULT NULL,
  reset_token VARCHAR(64) DEFAULT NULL,
  reset_token_expires DATETIME DEFAULT NULL,
  remember_token VARCHAR(64) DEFAULT NULL,
  is_banned TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_email (email),
  KEY idx_users_role (role_id),
  KEY idx_users_verification (verification_token),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB;

-- Categories
CREATE TABLE categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description TEXT DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_categories_slug (slug)
) ENGINE=InnoDB;

-- Tags
CREATE TABLE tags (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tags_slug (slug),
  KEY idx_tags_name (name)
) ENGINE=InnoDB;

-- Articles
CREATE TABLE articles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(280) NOT NULL,
  excerpt TEXT DEFAULT NULL,
  content LONGTEXT NOT NULL,
  cover_image VARCHAR(255) DEFAULT NULL,
  status ENUM('draft','published','scheduled') NOT NULL DEFAULT 'draft',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_breaking TINYINT(1) NOT NULL DEFAULT 0,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  published_at DATETIME DEFAULT NULL,
  scheduled_at DATETIME DEFAULT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(320) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_articles_slug (slug),
  KEY idx_articles_status_published (status, published_at),
  KEY idx_articles_category (category_id),
  KEY idx_articles_author (author_id),
  KEY idx_articles_featured (is_featured),
  KEY idx_articles_title (title),
  FULLTEXT KEY ft_articles_search (title, excerpt, content),
  CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES users (id),
  CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB;

-- Article Tags (pivot)
CREATE TABLE article_tags (
  article_id INT UNSIGNED NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (article_id, tag_id),
  KEY idx_article_tags_tag (tag_id),
  CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
  CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comments
CREATE TABLE comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  article_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED DEFAULT NULL,
  body TEXT NOT NULL,
  status ENUM('pending','approved','rejected','spam') NOT NULL DEFAULT 'pending',
  likes INT UNSIGNED NOT NULL DEFAULT 0,
  dislikes INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comments_article (article_id),
  KEY idx_comments_user (user_id),
  KEY idx_comments_status (status),
  KEY idx_comments_parent (parent_id),
  CONSTRAINT fk_comments_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comment votes
CREATE TABLE comment_votes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  comment_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  vote TINYINT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_comment_vote (comment_id, user_id),
  CONSTRAINT fk_comment_votes_comment FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE,
  CONSTRAINT fk_comment_votes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bookmarks
CREATE TABLE bookmarks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  article_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_bookmark (user_id, article_id),
  CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_bookmarks_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Contact messages
CREATE TABLE contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  subject VARCHAR(200) NOT NULL,
  category VARCHAR(80) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','resolved') NOT NULL DEFAULT 'new',
  admin_reply TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_status (status),
  CONSTRAINT fk_contact_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Site settings
CREATE TABLE site_settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_settings_key (setting_key)
) ENGINE=InnoDB;

-- Activity logs
CREATE TABLE activity_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) DEFAULT NULL,
  entity_id INT UNSIGNED DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_activity_user (user_id),
  KEY idx_activity_created (created_at),
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Login rate limiting
CREATE TABLE login_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(150) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_email_ip (email, ip_address, attempted_at)
) ENGINE=InnoDB;

-- Newsletter subscribers
CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(150) NOT NULL,
  subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_newsletter_email (email)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed data
INSERT INTO roles (id, name, slug, description) VALUES
(1, 'Admin', 'admin', 'Full system access'),
(2, 'Editor', 'editor', 'Manage articles and moderate comments'),
(3, 'Author', 'author', 'Create and edit own articles'),
(4, 'User', 'user', 'Regular registered user');

-- Password: Admin@123456 (bcrypt)
INSERT INTO users (role_id, name, email, password_hash, email_verified_at, bio) VALUES
(1, 'System Admin', 'admin@newshd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 'Chief administrator of NEWSHD.'),
(2, 'Editor One', 'editor@newshd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 'Senior news editor.'),
(3, 'Ram Sharma', 'author@newshd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 'Political correspondent.'),
(4, 'Demo User', 'user@newshd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 'Regular news reader.');

INSERT INTO categories (name, slug, description, sort_order) VALUES
('Politics', 'politics', 'National and local political news', 1),
('Sports', 'sports', 'Sports coverage and highlights', 2),
('Business', 'business', 'Economy, markets, and business', 3),
('Technology', 'technology', 'Tech innovations and digital trends', 4),
('Entertainment', 'entertainment', 'Movies, music, and culture', 5),
('World', 'world', 'International news', 6);

INSERT INTO tags (name, slug) VALUES
('Election', 'election'),
('Economy', 'economy'),
('Football', 'football'),
('AI', 'ai'),
('Health', 'health'),
('Climate', 'climate');

INSERT INTO articles (author_id, category_id, title, slug, excerpt, content, cover_image, status, is_featured, is_breaking, view_count, published_at, meta_title, meta_description) VALUES
(3, 1, 'Government Announces New National Development Policies', 'government-announces-new-national-development-policies',
 'The cabinet approved a comprehensive policy framework aimed at infrastructure, education, and digital transformation.',
 '<p>The government has unveiled a sweeping set of national development policies designed to accelerate economic growth and improve public services across the country.</p><p>Key highlights include increased investment in rural infrastructure, expanded scholarship programs, and a national digital literacy initiative targeting one million citizens within two years.</p><p>Opposition leaders have welcomed some measures while calling for stronger oversight and transparency in fund allocation.</p>',
 'https://images.unsplash.com/photo-1495020689067-958852a7765e?q=80&w=1200', 'published', 1, 1, 1240, NOW() - INTERVAL 2 DAY,
 'New National Development Policies | NEWSHD', 'Latest government policy announcements and national development plans.'),

(3, 4, 'AI and Modern Technology Are Reshaping Journalism', 'ai-modern-technology-reshaping-journalism',
 'Artificial intelligence tools are transforming how newsrooms gather, verify, and publish stories.',
 '<p>News organizations worldwide are adopting AI-powered tools for transcription, data analysis, and personalized content delivery.</p><p>Industry experts emphasize that human editorial judgment remains essential, while automation handles repetitive tasks and surfaces patterns in large datasets.</p><p>NEWSHD explores how regional newsrooms can adopt these technologies responsibly.</p>',
 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200', 'published', 1, 0, 890, NOW() - INTERVAL 1 DAY,
 'AI Reshaping Journalism | NEWSHD', 'How AI is changing modern journalism and newsrooms.'),

(3, 2, 'International Sports Championship: Highlights and Results', 'international-sports-championship-highlights',
 'Full coverage of match results, standout performances, and upcoming fixtures.',
 '<p>The international championship concluded with dramatic finishes across multiple disciplines.</p><p>Regional athletes secured medal positions in athletics and team sports, drawing record viewership on broadcast and digital platforms.</p><p>Coaches and analysts break down key moments and what to expect in the next round.</p>',
 'https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=1200', 'published', 0, 0, 654, NOW() - INTERVAL 5 HOUR,
 'Sports Championship Highlights | NEWSHD', 'Latest sports championship results and highlights.'),

(2, 3, 'Markets Rally as Economic Indicators Show Growth', 'markets-rally-economic-indicators-growth',
 'Stock indices climbed amid positive employment and export data released this quarter.',
 '<p>Financial markets responded positively to new economic indicators showing steady GDP growth and improved export figures.</p><p>Analysts note cautious optimism as inflation remains within target ranges and foreign investment flows increase.</p>',
 'https://images.unsplash.com/photo-1611974780655-e782c7737810?q=80&w=1200', 'published', 0, 0, 432, NOW() - INTERVAL 3 DAY,
 'Markets Rally on Growth Data | NEWSHD', 'Business and market news from NEWSHD.'),

(2, 5, 'Film Festival Opens With Record International Participation', 'film-festival-record-participation',
 'This year''s festival features premieres from over 40 countries and a focus on emerging filmmakers.',
 '<p>The annual film festival kicked off with a star-studded opening ceremony and a diverse lineup of screenings, panels, and workshops.</p><p>Organizers highlighted expanded grants for first-time directors and a new streaming partnership for selected titles.</p>',
 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200', 'published', 0, 0, 321, NOW() - INTERVAL 4 DAY,
 'Film Festival 2026 | NEWSHD', 'Entertainment coverage from NEWSHD.'),

(3, 6, 'Global Summit Addresses Climate and Trade Agreements', 'global-summit-climate-trade-agreements',
 'World leaders convened to discuss coordinated climate action and revised trade frameworks.',
 '<p>Delegates from dozens of nations participated in a high-level summit focused on renewable energy targets and fair trade practices.</p><p>Early agreements include joint research funding and standardized carbon reporting for major industries.</p>',
 'https://images.unsplash.com/photo-1529107386315-e1a2ecc4789f?q=80&w=1200', 'published', 1, 0, 567, NOW() - INTERVAL 6 HOUR,
 'Global Climate Summit | NEWSHD', 'World news and international summit coverage.');

INSERT INTO article_tags (article_id, tag_id) VALUES
(1, 1), (1, 2), (2, 4), (3, 3), (4, 2), (5, 6), (6, 6);

INSERT INTO comments (article_id, user_id, body, status, likes) VALUES
(1, 4, 'Important update. Hope implementation is transparent.', 'approved', 5),
(1, 4, 'Looking forward to the digital literacy program details.', 'approved', 2),
(2, 4, 'AI is useful but editorial ethics must come first.', 'approved', 8);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'NEWSHD'),
('site_tagline', 'देशको खबर, जनताको आवाज'),
('site_description', 'Trusted digital news portal delivering fast, reliable and accurate news.'),
('logo_url', 'assets/images/news-logo.jpg'),
('contact_email', 'contact@newshd.com'),
('contact_phone', '+977-1-1234567'),
('contact_address', 'Kathmandu, Nepal'),
('facebook_url', 'https://facebook.com/newshd'),
('twitter_url', 'https://twitter.com/newshd'),
('youtube_url', 'https://youtube.com/newshd'),
('theme_primary', '#d62828'),
('meta_keywords', 'news, politics, sports, business, technology, Nepal');
