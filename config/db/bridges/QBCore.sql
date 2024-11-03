-- Select data from players and insert into characters
INSERT INTO `characters` (discord, steamid, first_name, last_name, dob, gender, driverslicense)
SELECT 
    SUBSTRING_INDEX(p.`license`, ':', -1) AS discord, -- Assuming license has a format with discord/steam id, update as needed
    SUBSTRING_INDEX(p.`license`, ':', -1) AS steamid, -- Same assumption for steamid
    JSON_UNQUOTE(JSON_EXTRACT(p.`charinfo`, '$.firstname')) AS first_name,
    JSON_UNQUOTE(JSON_EXTRACT(p.`charinfo`, '$.lastname')) AS last_name,
    JSON_UNQUOTE(JSON_EXTRACT(p.`charinfo`, '$.birthdate')) AS dob,
    JSON_UNQUOTE(JSON_EXTRACT(p.`charinfo`, '$.gender')) AS gender,
    JSON_UNQUOTE(JSON_EXTRACT(p.`metadata`, '$.driverslicense')) AS driverslicense
FROM `players` p;
