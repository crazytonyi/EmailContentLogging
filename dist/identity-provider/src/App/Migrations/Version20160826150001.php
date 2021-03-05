<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\IdentityProvider\App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Current migrations creates user's table on 'up' and drop it on 'down'
 */
class Version20160826150001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE tenants
            (
              id CHAR(10) NOT NULL,
              create_time DATETIME,
              modify_time DATETIME,
              display_name VARCHAR(64) NOT NULL,
              region VARCHAR(64) NOT NULL,
              status TINYINT(1) DEFAULT 0,
              providers LONGTEXT,
              logo LONGTEXT,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
        $this->addSql('
            CREATE TABLE tenant_providers
            (
              tenant_id CHAR(10) NOT NULL,
              provider_code VARCHAR(50) NOT NULL,
              config LONGTEXT,
              attribute_map LONGTEXT,
              PRIMARY KEY (tenant_id, provider_code),
              FOREIGN KEY fk_tenant_providers_tenants (tenant_id) REFERENCES tenants(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
        $this->addSql('
            CREATE TABLE users
            (
              tenant_id CHAR(10) NOT NULL,
              id CHAR(36) NOT NULL,
              create_time DATETIME,
              modify_time DATETIME,
              password_hash VARCHAR(255),
              status TINYINT(1) DEFAULT 0,
              attributes JSON,
              custom_attributes JSON,
              last_login DATETIME DEFAULT NULL,
              login_attempts MEDIUMINT UNSIGNED DEFAULT 0,
              password_last_changed DATETIME DEFAULT NULL,
              lockout_time DATETIME DEFAULT NULL,
              is_locked_out BOOL DEFAULT false,
              failed_login_attempts MEDIUMINT UNSIGNED DEFAULT 0,
              user_type TINYINT(1) DEFAULT 0,
              created_by VARCHAR(255) NOT NULL,
              modified_by VARCHAR(255) NOT NULL,
              email              VARCHAR(100) GENERATED ALWAYS AS (attributes->>"$.email"),
              given_name         VARCHAR(100) GENERATED ALWAYS AS (attributes->>"$.given_name"),
              family_name        VARCHAR(100) GENERATED ALWAYS AS (attributes->>"$.family_name"),
              full_name          VARCHAR(200) GENERATED ALWAYS AS (TRIM(CONCAT(IFNULL((attributes->>"$.given_name"), ""), " ", IFNULL((attributes->>"$.family_name"), "")))),
              phone_number       VARCHAR(100) GENERATED ALWAYS AS (attributes->>"$.phone_number"),
              title              VARCHAR(100) GENERATED ALWAYS AS (attributes->>"$.title"),
              department         VARCHAR(100) GENERATED ALWAYS AS (attributes->>"$.department"),
              PRIMARY KEY (tenant_id, id),
              KEY idx_users_create_time (create_time),
              KEY idx_user_email_search (tenant_id,email),
              KEY idx_user_given_name_search (tenant_id,given_name),
              KEY idx_user_family_name_search (tenant_id,family_name),
              KEY idx_user_full_name_search (tenant_id,full_name),
              KEY idx_user_phone_number_search (tenant_id,phone_number),
              KEY idx_user_title_search (tenant_id,title),
              KEY idx_user_department_search (tenant_id,department),
              FOREIGN KEY fk_users_tenants (tenant_id) REFERENCES tenants(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
        $this->addSql('
            CREATE TABLE user_providers
            (
              tenant_id CHAR(10) NOT NULL,
              user_id CHAR(36) NOT NULL,
              provider_code VARCHAR(50) NOT NULL,
              identity_value VARCHAR(255),
              PRIMARY KEY (tenant_id, user_id, provider_code),
              KEY idx_user_providers_search (tenant_id, identity_value),
              CONSTRAINT idx_user_providers_identity UNIQUE (tenant_id, provider_code, identity_value),
              CONSTRAINT fk_user_providers_users FOREIGN KEY (tenant_id, user_id) REFERENCES users(tenant_id, id) ON DELETE CASCADE              
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
        $this->addSql('
            CREATE TABLE consents
            (
              tenant_id CHAR(10) NOT NULL,
              client_id VARCHAR(255) NOT NULL,
              scopes LONGTEXT,
              PRIMARY KEY (tenant_id, client_id),
              FOREIGN KEY fk_consents_tenants (tenant_id) REFERENCES tenants(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
        $this->addSql('
        CREATE TABLE sessions
        (
          session_id VARCHAR(128) NOT NULL,
          session_value BLOB NULL,
          session_lifetime INTEGER UNSIGNED NULL,
          session_time int UNSIGNED NULL,
          PRIMARY KEY (session_id),
          INDEX EXPIRY (session_lifetime)
        ) ENGINE=InnoDb DEFAULT CHARSET=utf8;
        ');

        $this->addSql('
        CREATE TABLE one_time_token
        (
          token VARCHAR(255) NOT NULL,
          tenant_id CHAR(10) NOT NULL,
          user_id CHAR(36) NOT NULL,
          expire_time DATETIME,
          PRIMARY KEY (token, tenant_id, user_id),
          CONSTRAINT `fk_one_time_token_users` FOREIGN KEY (tenant_id, user_id) 
          REFERENCES users(tenant_id, id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');

        $this->addSql('
        CREATE TABLE tenant_metadata
        (
            tenant_id CHAR(10) NOT NULL,
            metadata_section VARCHAR(255) NOT NULL,
            metadata longtext NULL,
            metadata_hash VARCHAR(255) NULL,
            metadata_source VARCHAR(255) NULL,
            PRIMARY KEY (tenant_id, metadata_section),
            CONSTRAINT tenant_metadata_tenants FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE `consents`;');
        $this->addSql('DROP TABLE `user_providers`;');
        $this->addSql('DROP TABLE `users`;');
        $this->addSql('DROP TABLE `tenant_providers`;');
        $this->addSql('DROP TABLE `tenants`;');
        $this->addSql('DROP TABLE `sessions`;');
        $this->addSql('DROP TABLE `one_time_token`;');
        $this->addSql('DROP TABLE `tenant_metadata`;');
    }
}
