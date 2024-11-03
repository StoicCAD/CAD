use stoiccad;

ALTER TABLE reports DROP FOREIGN KEY reports_ibfk_1; -- Replace with the actual constraint name if different
ALTER TABLE reports DROP COLUMN character_id;
ALTER TABLE reports ADD COLUMN user_id INT;
