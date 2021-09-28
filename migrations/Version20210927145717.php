<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210927145717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ApiKeys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE api_key (
                id VARCHAR(32) NOT NULL, 
                owner_id VARCHAR(32) NOT NULL, 
                label VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('CREATE INDEX IDX_C912ED9D7E3C61F9 ON api_key (owner_id)');
        $this->addSql('
            ALTER TABLE api_key 
                ADD CONSTRAINT FK_C912ED9D7E3C61F9 
                FOREIGN KEY (owner_id) REFERENCES "user" (id) 
                NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_key');
    }
}
