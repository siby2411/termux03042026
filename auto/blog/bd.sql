CREATE DATABASE `blog`;
Use `blog`;
CREATE TABLE `messages` (
 `id` int(20) NOT NULL,
 `sujet` varchar(255) NOT NULL,
 `message` text NOT NULL,
 `vue` int(1) DEFAULT 0 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `messages`
 ADD PRIMARY KEY (`id`);
ALTER TABLE `messages`
 MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;