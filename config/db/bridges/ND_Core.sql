-- After you import your CAD/config/import.sql USE this
-- To merge your ND_Characters into the CAD DB. 

INSERT INTO characters (discord, steamid, first_name, last_name, dob, gender)
SELECT 
    identifier AS discord,  -- Assuming identifier maps to discord; modify if necessary
    NULL AS steamid,        -- Assuming steamid is not available; set to NULL or provide a mapping if available
    firstname AS first_name,
    lastname AS last_name,
    dob,
    gender
FROM nd_characters;
