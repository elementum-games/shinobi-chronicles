<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DuplicateVoteMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table vote_logs
            ALTER TABLE `vote_logs` ADD CONSTRAINT `unique_user_proposal_combination` UNIQUE (user_id, proposal_id);
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table vote_logs
            ALTER TABLE `vote_logs` DROP CONSTRAINT `unique_user_proposal_combination`;
        ");
    }
}
