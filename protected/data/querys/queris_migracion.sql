
# query 1

ALTER TABLE `nigma`.`F_Imp` 
DROP FOREIGN KEY `fk_F_Impressions_D_UserAgent2`,
DROP FOREIGN KEY `fk_F_Impressions_D_GeoLocation2`;
ALTER TABLE `nigma`.`F_Imp` 
DROP INDEX `fk_F_Impressions_D_UserAgent2_idx` ,
DROP INDEX `fk_F_Impressions_D_GeoLocation2_idx` ;

# query 2

ALTER TABLE `nigma`.`F_Imp` 
CHANGE COLUMN `D_GeoLocation_id` `D_GeoLocation_id` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `D_UserAgent_id` `D_UserAgent_id` VARCHAR(255) NOT NULL ,
ADD COLUMN `imps` INT NULL DEFAULT 1 AFTER `referer_app`;

ALTER TABLE `nigma`.`F_Imp` 
ADD COLUMN `imps` INT NULL DEFAULT 1 AFTER `referer_app`;

# query 3

ALTER TABLE `nigma`.`D_GeoLocation` 
CHANGE COLUMN `id` `id` VARCHAR(255) NOT NULL ;

# query 4

ALTER TABLE `nigma`.`D_UserAgent` 
CHANGE COLUMN `id` `id` VARCHAR(255) NOT NULL ;



CREATE TABLE `F_Imp_Compact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `D_Demand_id` int(11) NOT NULL,
  `D_Supply_id` int(11) NOT NULL,
  `D_GeoLocation_id` varchar(255) COLLATE utf8_bin NOT NULL,
  `D_UserAgent_id` varchar(255) COLLATE utf8_bin NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unique_id` varchar(40) COLLATE utf8_bin NOT NULL,
  `pubid` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `ip_forwarded` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `referer_url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `referer_app` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `imps` int(11) DEFAULT '1',
  `unique_imps` int(11) DEFAULT '1',
  `revenue` decimal(11,6) DEFAULT '0.000000',
  `cost` decimal(11,6) DEFAULT '0.000000',
  PRIMARY KEY (`id`)
  KEY `fk_F_Impressions_D_Supply2_idx` (`D_Supply_id`),
  KEY `fk_F_Impressions_D_Demand2` (`D_Demand_id`),
  KEY `idx_F_Imp_date_time` (`date_time`),
  CONSTRAINT `fk_F_Impressions_D_Demand2` FOREIGN KEY (`D_Demand_id`) REFERENCES `D_Demand` (`tag_id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_F_Impressions_D_Supply2` FOREIGN KEY (`D_Supply_id`) REFERENCES `D_Supply` (`placement_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `F_Imp_Compact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `D_Demand_id` int(11) NOT NULL,
  `D_Supply_id` int(11) NOT NULL,
  `D_GeoLocation_id` varchar(255) COLLATE utf8_bin NOT NULL,
  `D_UserAgent_id` varchar(255) COLLATE utf8_bin NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unique_id` varchar(40) COLLATE utf8_bin NOT NULL,
  `pubid` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `ip_forwarded` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `referer_url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `referer_app` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `imps` int(11) DEFAULT '1',
  `unique_imps` int(11) DEFAULT '1',
  `revenue` decimal(11,6) DEFAULT '0.000000',
  `cost` decimal(11,6) DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `fk_F_Impressions_D_Supply2_idx` (`D_Supply_id`),
  KEY `fk_F_Impressions_D_Demand2` (`D_Demand_id`),
  KEY `idx_F_Imp_date_time` (`date_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


# compact query
# 
INSERT INTO F_Imp_Compact
	(
		D_Demand_id,
		D_Supply_id,
		D_Geolocation_id,
		D_UserAgent_id,
		imps,
		date_time,
		unique_id,
		unique_imps,
        revenue,
        cost
    )
SELECT
	i.D_Demand_id AS D_Demand_id, 
	i.D_Supply_id AS D_Supply_id,
    i.D_Geolocation_id AS D_Geolocation_id,
    i.D_UserAgent_id AS D_UserAgent_id,
	count(i.id) AS imps,
    i.date_time AS date_time,
    i.unique_id AS unique_id,
    count(distinct i.unique_id) AS unique_imps,
    sum(b.revenue) AS revenue,
    sum(b.cost) AS cost

FROM F_Imp i

LEFT JOIN D_GeoLocation g ON (g.id = i.D_GeoLocation_id)
LEFT JOIN D_UserAgent a ON (a.id = i.D_UserAgent_id)
LEFT JOIN D_Bid b ON (b.F_Impressions_id = i.id)

WHERE month(date_time)=2 AND year(date_time)=2017 
GROUP BY i.D_Demand_id, i.D_Supply_id, g.country, a.os_type, 



# update new columns compact

UPDATE F_Imp_Compact i 
LEFT JOIN D_GeoLocation g ON (g.id = i.D_GeoLocation_id)
LEFT JOIN D_UserAgent a ON (a.id = i.D_UserAgent_id)
SET 
i.os_type = a.os_type,
i.country = g.country,
i.connection_type = CASE
WHEN g.connection_type = 'WIFI' THEN 'WIFI'
WHEN g.connection_type = '3G' THEN 'MOBILE'
WHEN g.connection_type = '' THEN NULL
ELSE NULL
END
WHERE YEAR(i.date_time) = 2016 AND MONTH(i.date_time) = 2



# update new columns not compact

UPDATE F_Imp_Compact i 
LEFT JOIN D_GeoLocation g ON (g.id = i.D_GeoLocation_id)
LEFT JOIN D_UserAgent a ON (a.id = i.D_UserAgent_id)
SET 
i.os_type = a.os_type,
i.user_agent = a.user_agent,
i.device_type = a.device_type,
i.device_brand = a.device_brand,
i.device_model = a.device_model,
i.os_version = a.os_version,
i.browser_type = a.browser_type,
i.browser_version = a.browser_version,
i.server_ip = g.server_ip,
i.carrier = g.carrier,
i.country = g.country,
i.connection_type = CASE
WHEN g.connection_type = 'WIFI' THEN 'WIFI'
WHEN g.connection_type = '3G' THEN 'MOBILE'
WHEN g.connection_type = '' THEN NULL
ELSE NULL
END
where i.id > 205654806 and i.server_ip is null and i.user_agent is null and DATE(i.date_time) >= '2017-05-31' 


# check etl 2 inserts
# 
select 
SEC_TO_TIME( unix_timestamp( now() ) - unix_timestamp('2017-05-30 19:37:00') ) as time, 
max(id)-205654806 as inserts 
from F_Imp_Compact;

# old nigma imps
select 
provider as Publisher, 
tags_id as TagID, 
count(*) as Imps 
from imp_log i 
left join D_Supply s on i.placements_id=s.placement_id 
where date(date)=curdate() 
group by provider, tags_id;

# select pubid by publisher
select s.provider, pubid, sum(imps) from F_Imp_Compact i 
left join D_Supply s on i.D_Supply_id = s.placement_id 
where pubid in('x','x') and date(date_time)=subdate(curdate(),1) 
group by s.provider, pubid;

# select pubid by publisher / country
select s.provider, i.country, pubid, sum(imps) from F_Imp_Compact i 
left join D_Supply s on i.D_Supply_id = s.placement_id 
where pubid in('x','x') and date(date_time)=subdate(curdate(),1) 
group by s.provider, i.country, pubid;

# find null pid
select D_Demand_id, count(imps) from F_Imp_Compact 
where D_Supply_id is null and date(date_time)=subdate(curdate(),1) 
group by D_Demand_id;

select D_Demand_id, D_Supply_id, count(imps) from F_Imp_Compact 
where D_Demand_id in (245, 248, 253, 255, 258, 260) 
and date(date_time)=subdate(curdate(),0) 
group by D_Supply_id;

# bid compact
UPDATE F_Imp_Compact i 
LEFT JOIN D_Demand d ON(i.D_Demand_id = d.tag_id) 
LEFT JOIN D_Supply s ON(i.D_Supply_id = s.placement_id) 
SET 
i.revenue = CASE 
WHEN d.freq_cap IS NULL THEN d.rate/1000 * i.imps 
WHEN d.freq_cap > i.imps THEN d.rate/1000 * i.imps 
ELSE d.rate/1000 * d.freq_cap END, 
i.cost = CASE 
WHEN d.freq_cap IS NULL THEN s.rate/1000 * i.imps 
WHEN d.freq_cap > i.imps THEN s.rate/1000 * i.imps 
ELSE s.rate/1000 * d.freq_cap END 
WHERE (i.connection_type = d.connection_type OR d.connection_type IS NULL OR d.connection_type = "") 
AND (i.country = d.country OR d.country IS NULL OR d.country = "") 
AND (i.os_type = d.os_type OR d.os_type IS NULL OR d.os_type = "") 
AND (CONVERT(i.os_version, DECIMAL(5,2)) >= CONVERT(d.os_version, DECIMAL(5,2)) OR d.os_version IS NULL OR d.os_version = "") 
AND i.D_Demand_id in (261, 262, 263, 264, 266, 267, 268, 269, 272, 273)
AND id <= '226479666' 
AND i.revenue = 0 
AND i.cost = 0

AND DATE(i.date_time) = "2017-06-07"


update F_Imp_Compact set D_Supply_id = 2156 where D_Demand_id = 245;



SELECT d.advertiser, 
sum(imps) as imps, 
FLOOR(sum(imps)/2) as imps_corrected, 
sum(revenue) as revenue, 
sum(revenue)/2 as revenue_corrected
FROM F_Imp_Compact i 
LEFT JOIN D_Demand d ON(i.D_Demand_id = d.tag_id) 
WHERE date(date_time) between '2017-05-26' and '2017-06-06' 
AND (ad_server_id = 2 or ad_server_id is null) 
AND d.advertiser = 'GGL (20)'
AND i.D_Demand_id != 140 

GROUP BY d.advertiser


ADD INDEX `country` (`country` ASC),
ADD INDEX `device_brand` (`device_brand` ASC),
ADD INDEX `device_model` (`device_model` ASC),
ADD INDEX `os_type` (`os_type` ASC),
ADD INDEX `os_version` (`os_version` ASC),
ADD INDEX `browser_type` (`browser_type` ASC),
ADD INDEX `browser_version` (`os_version` ASC),

ALTER TABLE `nigma`.`F_Imp_Compact` 
ADD INDEX `carrier` (`carrier` ASC),
ADD INDEX `connection_type` (`connection_type` ASC)



#
ALTER TABLE `nigma`.`F_Imp_Compact` 
DROP COLUMN `D_UserAgent_id`,
DROP COLUMN `D_GeoLocation_id`



# compact data 
#261251997

SET @compactdate := '2017-06-08';
SET @maxid := (SELECT MAX(id) FROM F_Imp_Compact);

INSERT INTO F_Imp_Compact 
( ad_server_id, D_Demand_id, D_Supply_id, date_time, ad_req, imps, unique_imps, revenue, cost , country, connection_type, device_type, os_type, os_version ) 
SELECT ad_server_id, D_Demand_id AS D_Demand_id, D_Supply_id AS D_Supply_id, date_time AS date_time, SUM(imps) AS ad_req, SUM(imps) AS imps, SUM(unique_imps) AS unique_imps, SUM(revenue) AS revenue, SUM(cost) AS cost , country, connection_type, device_type, os_type, os_version 
FROM F_Imp_Compact 
WHERE date(date_time) = @compactdate 
GROUP BY ad_server_id, D_Demand_id, D_Supply_id , country, connection_type, device_type, os_type, os_version
;

DELETE FROM F_Imp_Compact WHERE date(date_time) = @compactdate AND id <= @maxid;

# chech data

select date(date_time), count(*), sum(imps), sum(unique_imps), sum(revenue), sum(cost) from F_Imp_Compact where date(date_time) = @compactdate and id > @maxid;
