<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SenseiKeyMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Step 1: Create a temporary table with only one sensei record per player, prioritizing active sensei records
            CREATE TEMPORARY TABLE temp_sensei_table AS
            SELECT *
            FROM sensei
            WHERE (sensei_id, is_active) IN (
                SELECT sensei_id, MAX(is_active)
                FROM sensei
                GROUP BY sensei_id
            )
            GROUP BY sensei_id;

            -- Clear table
            DELETE FROM sensei;

            -- Insert the filtered records back
            INSERT INTO sensei
            SELECT * FROM temp_sensei_table;

            -- Add primary key
            ALTER TABLE sensei
            ADD PRIMARY KEY (sensei_id);

            -- Drop the temp table
            DROP TABLE temp_sensei_table;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table sensei
            DROP PRIMARY KEY;
        ");
    }
}
