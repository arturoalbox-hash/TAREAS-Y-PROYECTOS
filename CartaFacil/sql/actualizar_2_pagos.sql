-- CartaFácil · Actualización 2: pagos mensuales desde el panel
-- Si ya tenías la base de datos creada, importa ESTE archivo en phpMyAdmin
-- (base de datos cartafacil → pestaña Importar). Si instalas desde cero no hace falta.

ALTER TABLE restaurants
  ADD COLUMN stripe_customer_id VARCHAR(64) NULL AFTER paid_until,
  ADD COLUMN stripe_subscription_id VARCHAR(64) NULL AFTER stripe_customer_id,
  ADD COLUMN subscription_status VARCHAR(20) NULL AFTER stripe_subscription_id;

ALTER TABLE restaurants ADD INDEX idx_rest_customer (stripe_customer_id);

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT UNSIGNED NOT NULL,
  stripe_invoice_id VARCHAR(64) NOT NULL UNIQUE,
  number VARCHAR(40) NULL,
  amount_cents INT NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'EUR',
  status VARCHAR(20) NOT NULL,
  period_end DATE NULL,
  hosted_url VARCHAR(500) NULL,
  pdf_url VARCHAR(500) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_pay_rest FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
  INDEX idx_pay_rest (restaurant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
