CREATE DATABASE IF NOT EXISTS sheet_music_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sheet_music_store;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer', 'composer', 'admin') NOT NULL DEFAULT 'customer',
  location VARCHAR(120) DEFAULT '',
  bio VARCHAR(255) DEFAULT 'Music Enthusiast',
  avatar VARCHAR(255) DEFAULT 'https://picsum.photos/150/150?random=100',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role ENUM('customer', 'composer', 'admin') NOT NULL DEFAULT 'customer' AFTER password;

CREATE TABLE IF NOT EXISTS composers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  period VARCHAR(80) NOT NULL,
  nationality VARCHAR(80) NOT NULL,
  image VARCHAR(255) NOT NULL,
  works INT UNSIGNED NOT NULL DEFAULT 0,
  biography TEXT NOT NULL,
  featured_work VARCHAR(150) NOT NULL,
  CONSTRAINT fk_composers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE composers
  ADD COLUMN IF NOT EXISTS user_id INT UNSIGNED NULL AFTER id;

CREATE TABLE IF NOT EXISTS composer_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('pending', 'approved', 'declined') NOT NULL DEFAULT 'pending',
  admin_id INT UNSIGNED NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_composer_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_composer_requests_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS scores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  composer VARCHAR(150) NOT NULL,
  genre VARCHAR(80) NOT NULL,
  difficulty VARCHAR(50) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  image VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  pages INT UNSIGNED NOT NULL DEFAULT 0,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS purchases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  score_id INT UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  purchase_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_purchases_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_purchases_score FOREIGN KEY (score_id) REFERENCES scores(id) ON DELETE CASCADE
);

INSERT INTO composers (id, name, period, nationality, image, works, biography, featured_work) VALUES
  (1, 'Ludwig van Beethoven', 'Classical/Romantic', 'German', 'https://picsum.photos/400/300?random=10', 138, 'One of the most influential composers in Western classical music.', 'Symphony No. 9'),
  (2, 'Frédéric Chopin', 'Romantic', 'Polish', 'https://picsum.photos/400/300?random=11', 230, 'Poet of the piano who transformed expressive keyboard writing.', 'Nocturnes'),
  (3, 'Claude Debussy', 'Impressionist', 'French', 'https://picsum.photos/400/300?random=12', 141, 'Pioneer of Impressionist music with a distinctive harmonic language.', 'Prélude à l''après-midi d''un faune'),
  (4, 'Johann Sebastian Bach', 'Baroque', 'German', 'https://picsum.photos/400/300?random=13', 1128, 'Master of counterpoint and one of the foundational figures in Western music.', 'Brandenburg Concertos'),
  (5, 'Wolfgang Amadeus Mozart', 'Classical', 'Austrian', 'https://picsum.photos/400/300?random=14', 626, 'A prolific classical composer whose works remain central to the repertoire.', 'The Magic Flute'),
  (6, 'Pyotr Ilyich Tchaikovsky', 'Romantic', 'Russian', 'https://picsum.photos/400/300?random=15', 169, 'The first Russian composer to achieve enduring international fame.', 'Swan Lake')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO scores (id, title, composer, genre, difficulty, price, image, description, pages, rating) VALUES
  (1, 'Moonlight Sonata', 'Ludwig van Beethoven', 'Classical', 'Advanced', 19.99, 'https://picsum.photos/400/300?random=1', 'Complete piano sonata No. 14 in C-sharp minor.', 23, 4.9),
  (2, 'Clair de Lune', 'Claude Debussy', 'Impressionist', 'Intermediate', 14.99, 'https://picsum.photos/400/300?random=2', 'From Suite Bergamasque, one of the most beloved piano pieces.', 15, 4.8),
  (3, 'Nocturne in E-flat Major', 'Frédéric Chopin', 'Romantic', 'Advanced', 17.99, 'https://picsum.photos/400/300?random=3', 'Op. 9 No. 2, one of Chopin''s most famous nocturnes.', 12, 4.9),
  (4, 'The Entertainer', 'Scott Joplin', 'Ragtime', 'Intermediate', 12.99, 'https://picsum.photos/400/300?random=4', 'Classic ragtime piece, perfect for intermediate pianists.', 8, 4.7),
  (5, 'Canon in D', 'Johann Pachelbel', 'Baroque', 'Beginner', 9.99, 'https://picsum.photos/400/300?random=5', 'Beautiful and accessible arrangement for piano.', 6, 4.6),
  (6, 'Rhapsody in Blue', 'George Gershwin', 'Jazz/Classical', 'Advanced', 24.99, 'https://picsum.photos/400/300?random=6', 'Iconic fusion of classical music with jazz elements.', 35, 4.9)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO users (id, name, email, password, role, location, bio, avatar) VALUES
  (1, 'John Doe', 'john.doe@email.com', '$2y$12$PPSfkVteIhMw93H79Asqz.uxja4SjkeipG9kunMlav257a9A9hF7K', 'customer', 'New York, USA', 'Music Enthusiast', 'https://picsum.photos/150/150?random=100'),
  (2, 'Admin User', 'admin@theresonanz.com', '$2y$12$PPSfkVteIhMw93H79Asqz.uxja4SjkeipG9kunMlav257a9A9hF7K', 'admin', 'Jakarta, Indonesia', 'Platform Administrator', 'https://picsum.photos/150/150?random=101')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO purchases (id, user_id, score_id, price, purchase_date) VALUES
  (1, 1, 1, 19.99, '2024-01-15'),
  (2, 1, 2, 14.99, '2023-12-20'),
  (3, 1, 3, 17.99, '2023-11-05')
ON DUPLICATE KEY UPDATE price = VALUES(price);
