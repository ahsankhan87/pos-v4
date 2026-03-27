START TRANSACTION;

CREATE TABLE IF NOT EXISTS pos_recurring_invoices (
id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
recurring_no VARCHAR(40) NOT NULL,
store_id INT(11) NOT NULL,
customer_id INT(11) NULL,
template_name VARCHAR(120) NOT NULL,
description TEXT NULL,
frequency VARCHAR(20) NOT NULL DEFAULT 'monthly',
monthly_mode VARCHAR(20) NOT NULL DEFAULT 'day_of_month',
day_of_month TINYINT(2) NULL,
start_date DATE NOT NULL,
end_date DATE NULL,
next_due_date DATETIME NULL,
last_generated_at DATETIME NULL,
last_sale_id INT(11) NULL,
payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
status VARCHAR(20) NOT NULL DEFAULT 'active',
items_json LONGTEXT NOT NULL,
subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
total_discount DECIMAL(15,2) NOT NULL DEFAULT 0,
total_tax DECIMAL(15,2) NOT NULL DEFAULT 0,
total DECIMAL(15,2) NOT NULL DEFAULT 0,
created_by INT(11) NULL,
updated_by INT(11) NULL,
created_at DATETIME NULL,
updated_at DATETIME NULL,
PRIMARY KEY (id),
UNIQUE KEY uq_pos_recurring_invoices_recurring_no (recurring_no),
KEY idx_pos_recurring_invoices_store_status_due (store_id, status, next_due_date),
KEY idx_pos_recurring_invoices_customer_status (customer_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$
DROP PROCEDURE IF EXISTS apply_recurring_invoices_patch $$
CREATE PROCEDURE apply_recurring_invoices_patch()
BEGIN
	-- 1060: duplicate column name
	DECLARE CONTINUE HANDLER FOR 1060 BEGIN END;
	-- 1061: duplicate key name
	DECLARE CONTINUE HANDLER FOR 1061 BEGIN END;

	ALTER TABLE pos_sales
		ADD COLUMN recurring_invoice_id INT(11) NULL;

	ALTER TABLE pos_sales
		ADD INDEX idx_recurring_invoice_id (recurring_invoice_id);
END $$
DELIMITER ;

CALL apply_recurring_invoices_patch();
DROP PROCEDURE IF EXISTS apply_recurring_invoices_patch;

COMMIT;