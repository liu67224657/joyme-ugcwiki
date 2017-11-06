
/*Table structure for table `smw_concept_cache` */

DROP TABLE IF EXISTS `smw_concept_cache`;

CREATE TABLE `smw_concept_cache` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned NOT NULL,
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_concept_cache` */

/*Table structure for table `smw_di_blob` */

DROP TABLE IF EXISTS `smw_di_blob`;

CREATE TABLE `smw_di_blob` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_blob` */

/*Table structure for table `smw_di_bool` */

DROP TABLE IF EXISTS `smw_di_bool`;

CREATE TABLE `smw_di_bool` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_value` tinyint(1) DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_bool` */

/*Table structure for table `smw_di_coords` */

DROP TABLE IF EXISTS `smw_di_coords`;

CREATE TABLE `smw_di_coords` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_lat` double DEFAULT NULL,
  `o_lon` double DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_serialized`),
  KEY `o_lat` (`o_lat`,`o_lon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_coords` */

/*Table structure for table `smw_di_number` */

DROP TABLE IF EXISTS `smw_di_number`;

CREATE TABLE `smw_di_number` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_number` */

/*Table structure for table `smw_di_time` */

DROP TABLE IF EXISTS `smw_di_time`;

CREATE TABLE `smw_di_time` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_time` */

/*Table structure for table `smw_di_uri` */

DROP TABLE IF EXISTS `smw_di_uri`;

CREATE TABLE `smw_di_uri` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_serialized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_uri` */

/*Table structure for table `smw_di_wikipage` */

DROP TABLE IF EXISTS `smw_di_wikipage`;

CREATE TABLE `smw_di_wikipage` (
  `s_id` int(8) unsigned NOT NULL,
  `p_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`,`p_id`),
  KEY `p_id` (`p_id`,`o_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_di_wikipage` */

/*Table structure for table `smw_fpt_ask` */

DROP TABLE IF EXISTS `smw_fpt_ask`;

CREATE TABLE `smw_fpt_ask` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_ask` */

/*Table structure for table `smw_fpt_askde` */

DROP TABLE IF EXISTS `smw_fpt_askde`;

CREATE TABLE `smw_fpt_askde` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_sortkey` (`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_askde` */

/*Table structure for table `smw_fpt_askdu` */

DROP TABLE IF EXISTS `smw_fpt_askdu`;

CREATE TABLE `smw_fpt_askdu` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_sortkey` (`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_askdu` */

/*Table structure for table `smw_fpt_askfo` */

DROP TABLE IF EXISTS `smw_fpt_askfo`;

CREATE TABLE `smw_fpt_askfo` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_askfo` */

/*Table structure for table `smw_fpt_asksi` */

DROP TABLE IF EXISTS `smw_fpt_asksi`;

CREATE TABLE `smw_fpt_asksi` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_sortkey` (`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_asksi` */

/*Table structure for table `smw_fpt_askst` */

DROP TABLE IF EXISTS `smw_fpt_askst`;

CREATE TABLE `smw_fpt_askst` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_askst` */

/*Table structure for table `smw_fpt_cdat` */

DROP TABLE IF EXISTS `smw_fpt_cdat`;

CREATE TABLE `smw_fpt_cdat` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_sortkey` (`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_cdat` */

/*Table structure for table `smw_fpt_conc` */

DROP TABLE IF EXISTS `smw_fpt_conc`;

CREATE TABLE `smw_fpt_conc` (
  `s_id` int(8) unsigned NOT NULL,
  `concept_txt` mediumblob,
  `concept_docu` mediumblob,
  `concept_features` int(11) DEFAULT NULL,
  `concept_size` int(11) DEFAULT NULL,
  `concept_depth` int(11) DEFAULT NULL,
  `cache_date` int(8) unsigned DEFAULT NULL,
  `cache_count` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_conc` */

/*Table structure for table `smw_fpt_conv` */

DROP TABLE IF EXISTS `smw_fpt_conv`;

CREATE TABLE `smw_fpt_conv` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_conv` */

/*Table structure for table `smw_fpt_dtitle` */

DROP TABLE IF EXISTS `smw_fpt_dtitle`;

CREATE TABLE `smw_fpt_dtitle` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_dtitle` */

/*Table structure for table `smw_fpt_impo` */

DROP TABLE IF EXISTS `smw_fpt_impo`;

CREATE TABLE `smw_fpt_impo` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_impo` */

/*Table structure for table `smw_fpt_inst` */

DROP TABLE IF EXISTS `smw_fpt_inst`;

CREATE TABLE `smw_fpt_inst` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_inst` */

/*Table structure for table `smw_fpt_lcode` */

DROP TABLE IF EXISTS `smw_fpt_lcode`;

CREATE TABLE `smw_fpt_lcode` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_lcode` */

/*Table structure for table `smw_fpt_ledt` */

DROP TABLE IF EXISTS `smw_fpt_ledt`;

CREATE TABLE `smw_fpt_ledt` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_ledt` */

/*Table structure for table `smw_fpt_list` */

DROP TABLE IF EXISTS `smw_fpt_list`;

CREATE TABLE `smw_fpt_list` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_list` */

/*Table structure for table `smw_fpt_mdat` */

DROP TABLE IF EXISTS `smw_fpt_mdat`;

CREATE TABLE `smw_fpt_mdat` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_sortkey` (`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_mdat` */

/*Table structure for table `smw_fpt_prec` */

DROP TABLE IF EXISTS `smw_fpt_prec`;

CREATE TABLE `smw_fpt_prec` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  `o_sortkey` double DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_sortkey` (`o_sortkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_prec` */

/*Table structure for table `smw_fpt_pval` */

DROP TABLE IF EXISTS `smw_fpt_pval`;

CREATE TABLE `smw_fpt_pval` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_pval` */

/*Table structure for table `smw_fpt_redi` */

DROP TABLE IF EXISTS `smw_fpt_redi`;

CREATE TABLE `smw_fpt_redi` (
  `s_title` varbinary(255) NOT NULL,
  `s_namespace` int(11) NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_title` (`s_title`,`s_namespace`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_redi` */

/*Table structure for table `smw_fpt_serv` */

DROP TABLE IF EXISTS `smw_fpt_serv`;

CREATE TABLE `smw_fpt_serv` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_serv` */

/*Table structure for table `smw_fpt_sobj` */

DROP TABLE IF EXISTS `smw_fpt_sobj`;

CREATE TABLE `smw_fpt_sobj` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_sobj` */

/*Table structure for table `smw_fpt_subc` */

DROP TABLE IF EXISTS `smw_fpt_subc`;

CREATE TABLE `smw_fpt_subc` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_subc` */

/*Table structure for table `smw_fpt_subp` */

DROP TABLE IF EXISTS `smw_fpt_subp`;

CREATE TABLE `smw_fpt_subp` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_subp` */

/*Table structure for table `smw_fpt_text` */

DROP TABLE IF EXISTS `smw_fpt_text`;

CREATE TABLE `smw_fpt_text` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_text` */

/*Table structure for table `smw_fpt_type` */

DROP TABLE IF EXISTS `smw_fpt_type`;

CREATE TABLE `smw_fpt_type` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_serialized` (`o_serialized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_type` */

/*Table structure for table `smw_fpt_unit` */

DROP TABLE IF EXISTS `smw_fpt_unit`;

CREATE TABLE `smw_fpt_unit` (
  `s_id` int(8) unsigned NOT NULL,
  `o_blob` mediumblob,
  `o_hash` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_hash` (`o_hash`),
  KEY `s_id_2` (`s_id`,`o_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_unit` */

/*Table structure for table `smw_fpt_uri` */

DROP TABLE IF EXISTS `smw_fpt_uri`;

CREATE TABLE `smw_fpt_uri` (
  `s_id` int(8) unsigned NOT NULL,
  `o_serialized` varbinary(255) DEFAULT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_serialized` (`o_serialized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_fpt_uri` */

/*Table structure for table `smw_object_ids` */

DROP TABLE IF EXISTS `smw_object_ids`;

CREATE TABLE `smw_object_ids` (
  `smw_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `smw_namespace` int(11) NOT NULL,
  `smw_title` varbinary(255) NOT NULL,
  `smw_iw` varbinary(32) NOT NULL,
  `smw_subobject` varbinary(255) NOT NULL,
  `smw_sortkey` varbinary(255) NOT NULL,
  `smw_proptable_hash` mediumblob,
  PRIMARY KEY (`smw_id`),
  KEY `smw_id` (`smw_id`,`smw_sortkey`),
  KEY `smw_iw` (`smw_iw`),
  KEY `smw_title` (`smw_title`,`smw_namespace`,`smw_iw`,`smw_subobject`),
  KEY `smw_sortkey` (`smw_sortkey`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

/*Data for the table `smw_object_ids` */

insert  into `smw_object_ids`(`smw_id`,`smw_namespace`,`smw_title`,`smw_iw`,`smw_subobject`,`smw_sortkey`,`smw_proptable_hash`) values (1,102,'_TYPE','','','Has type',NULL),(2,102,'_URI','','','Equivalent URI',NULL),(4,102,'_INST',':smw-intprop','','',NULL),(7,102,'_UNIT','','','Display units',NULL),(8,102,'_IMPO','','','Imported from',NULL),(10,102,'_PDESC','','','Has property description',NULL),(11,102,'_PREC','','','Display precision of',NULL),(12,102,'_CONV','','','Corresponds to',NULL),(13,102,'_SERV','','','Provides service',NULL),(14,102,'_PVAL','','','Allows value',NULL),(15,102,'_REDI',':smw-intprop','','',NULL),(16,102,'_DTITLE','','','Display title of',NULL),(17,102,'_SUBP','','','Subproperty of',NULL),(18,102,'_SUBC','','','Subcategory of',NULL),(19,102,'_CONC',':smw-intprop','','',NULL),(22,102,'_ERRP','','','Has improper value for',NULL),(28,102,'_LIST','','','Has fields',NULL),(29,102,'_MDAT','','','Modification date',NULL),(30,102,'_CDAT','','','Creation date',NULL),(31,102,'_NEWP','','','Is a new page',NULL),(32,102,'_LEDT','','','Last editor is',NULL),(33,102,'_ASK','','','Has query',NULL),(34,102,'_ASKST','','','Query string',NULL),(35,102,'_ASKFO','','','Query format',NULL),(36,102,'_ASKSI','','','Query size',NULL),(37,102,'_ASKDE','','','Query depth',NULL),(40,102,'_LCODE','','','Language code',NULL),(41,102,'_TEXT','','','Text',NULL),(50,0,'',':smw-border','','',NULL);

/*Table structure for table `smw_prop_stats` */

DROP TABLE IF EXISTS `smw_prop_stats`;

CREATE TABLE `smw_prop_stats` (
  `p_id` int(8) unsigned DEFAULT NULL,
  `usage_count` int(8) unsigned DEFAULT NULL,
  UNIQUE KEY `p_id` (`p_id`),
  KEY `usage_count` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_prop_stats` */

/*Table structure for table `smw_query_links` */

DROP TABLE IF EXISTS `smw_query_links`;

CREATE TABLE `smw_query_links` (
  `s_id` int(8) unsigned NOT NULL,
  `o_id` int(8) unsigned NOT NULL,
  KEY `s_id` (`s_id`),
  KEY `o_id` (`o_id`),
  KEY `s_id_2` (`s_id`,`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `smw_query_links` */
