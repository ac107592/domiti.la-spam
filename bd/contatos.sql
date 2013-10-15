SELECT nome, LCASE (email)
FROM Contatos
WHERE email IS NOT NULL AND email <> '' AND email REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'
ORDER BY nome, email;