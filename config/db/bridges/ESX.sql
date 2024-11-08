INSERT INTO `characters` (steamid, first_name, last_name, dob, gender)
SELECT 
  `identifier` AS `steamid`,          -- Steam ID or unique identifier
  `firstname` AS `first_name`,         -- First name
  `lastname` AS `last_name`,           -- Last name
  `dateofbirth` AS `dob`,              -- Date of birth
  CASE                                  -- Gender conversion
    WHEN `sex` = 'M' THEN 'Male'
    WHEN `sex` = 'F' THEN 'Female'
    ELSE 'Unknown'
  END AS `gender`
FROM `users`;