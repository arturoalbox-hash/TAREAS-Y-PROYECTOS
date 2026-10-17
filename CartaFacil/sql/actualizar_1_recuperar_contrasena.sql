-- CartaFácil · Actualización 1: recuperar contraseña
-- Si ya importaste mysql.sql antes, importa ESTE archivo en phpMyAdmin
-- (base de datos cartafacil → pestaña Importar). Si instalas desde cero no hace falta:
-- la tabla ya viene en mysql.sql.

CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_reset_rest FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
