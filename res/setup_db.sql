CREATE DATABASE cc;
CREATE USER 'cc_user'@'localhost' IDENTIFIED BY 'cc_passwd';
GRANT ALL PRIVILEGES ON cc.* TO 'cc_user'@'localhost';
#ALTER USER 'cc_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'cc_passwd'; # Does not work on the pi - needed it for Mac development setup
FLUSH PRIVILEGES;
