ALTER TABLE `s_plugin_sharebasket_baskets` ADD `hash` VARCHAR(255) NOT NULL AFTER `created`;
CREATE TABLE `s_plugin_sharebasket_articles` (
 `id` int(11) NOT NULL,
 `share_basket_id` int(11) DEFAULT NULL,
 `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `quantity` int(11) NOT NULL,
 `mode` int(11) NOT NULL,
 `attributes` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
