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
                label VARCHAR(255), 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('CREATE INDEX owner_id_idx ON api_key (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX owner_label_idx ON api_key (owner_id, label)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_key');
    }
}
