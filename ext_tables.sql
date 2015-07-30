#
# Table structure for table 'tx_charbeitsbeispiele_maxmedia'
#
CREATE TABLE tx_charbeitsbeispiele_maxmedia (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
        deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
        target int(11) DEFAULT '0' NOT NULL,
	title tinytext,
        subheadline tinytext,
	text text,
	link tinytext,
        image blob NOT NULL,
	parent_id int(11) DEFAULT '0' NOT NULL,
        isroot tinyint(4) DEFAULT '0' NOT NULL,
	animate tinyint(4) DEFAULT '0' NOT NULL,
        
	PRIMARY KEY (uid),
	KEY parent (pid)
);