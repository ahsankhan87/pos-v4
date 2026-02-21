START TRANSACTION;

UPDATE pos_customer_ledger l
JOIN (
SELECT
x.id,
ROUND(
COALESCE(c.opening_balance, 0) +
SUM(COALESCE(x.debit, 0) - COALESCE(x.credit, 0))
OVER (
PARTITION BY x.customer_id
ORDER BY x.date, x.id
ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
),
2
) AS new_balance
FROM pos_customer_ledger x
LEFT JOIN pos_customers c ON c.id = x.customer_id
) r ON r.id = l.id
SET l.balance = r.new_balance;

COMMIT;