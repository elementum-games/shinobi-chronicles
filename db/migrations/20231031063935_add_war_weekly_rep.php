<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddWarWeeklyRep extends AbstractMigration {
    public function up(): void {
        $this->execute("
            alter table `users`
                change `weekly_rep` `weekly_pve_rep` int default 0 not null;
            
            alter table `users`
                add `weekly_war_rep` int default 0 not null after `weekly_pve_rep`;
            
            alter table `users`
                change `pvp_rep` `weekly_pvp_rep` int default 0 null;
        ");
    }

    public function down(): void {
        $this->execute("
            alter table `users`
                change `weekly_pve_rep` `weekly_rep` int default 0 not null;
            
            alter table `users`
                drop column `weekly_war_rep`;
            
            alter table `users`
                change `weekly_pvp_rep` `pvp_rep` int default 0 null;
        ");
    }
}
