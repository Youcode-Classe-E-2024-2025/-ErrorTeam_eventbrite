CREATE DATABASE eventbrite;
\c eventbrite;

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";


CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    role VARCHAR(50) NOT NULL check(role in ('admin','organiser','guest')) default 'guest',
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100),
    avatar TEXT,
    is_active BOOLEAN default true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    search_vector TSVECTOR GENERATED ALWAYS AS (to_tsvector('english', username || ' ' || email)) STORED
);


CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);
CREATE TABLE tags (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE events (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organizer_id UUID NOT NULL REFERENCES users(id),
    category_id UUID REFERENCES categories(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    location VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    search_vector TSVECTOR GENERATED ALWAYS AS (to_tsvector('english', title || ' ' || description)) STORED
);
CREATE TABLE event_tags (
    event_id uuid not null REFERENCES events(id),
    tag_id uuid not null REFERENCES tags(id)
);


CREATE TABLE reservations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id),
    event_id UUID NOT NULL REFERENCES events(id),
    quantity INT NOT NULL CHECK (quantity > 0),
    total_price DECIMAL(10, 2) NOT NULL,
    qr_code TEXT,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id),
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);




INSERT INTO categories (name, description) VALUES
('Conference', 'Professional gatherings'),
('Concert', 'Music performances'),
('Sport', 'Sports events');
