ALTER TABLE `users`
	ADD COLUMN `Role` VARCHAR(50) NOT NULL AFTER `Password`;
	
UPDATE users SET Role = 'calendar';

INSERT INTO users(UserName, Password, Role) VALUES ('newsGuy', '09bf4e19609d8139bfb99744c67e5088', 'news');


n3w$Th1ngs