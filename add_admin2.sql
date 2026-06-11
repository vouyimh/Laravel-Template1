INSERT INTO users (name, email, email_verified_at, password, role, created_at, updated_at)
VALUES (
    'Admin Two',
    'admin2@bionett.com',
    NOW(),
    '$2y$12$f3TnBQYWh3kQx5gPVuR8G.6iOTK2HlTIxvITwvosSCgm7DudLkw/G',
    'admin',
    NOW(),
    NOW()
);
