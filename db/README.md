(copied from main README.md)

We use [Phinx](https://phinx.org/) for simple migration management. The documentation on writing migrations is here:
https://book.cakephp.org/phinx/0/en/migrations.html

**Initial Setup**  
- Make sure you have installed Composer and run `composer install` to install Phinx.
- Run `vendor/bin/phinx seed:run` to populate your database with tables/data.
  - If you have existing database structure, this will not run. You need to drop all tables and re-run the command.
    
**Quick Cheatsheet**
- Run all migrations
    - `vendor/bin/phinx migrate`
    - Useful if you've just pulled some changes from GitHub that include DB migrations
- Create a migration
    - `vendor/bin/phinx create MyCoolMigration`
    - If your DB is up to date except for this new migration, you can run the `vendor/bin/phinx migrate` command to execute it
- Run all migrations up to a specific version
    - `vendor/bin/phinx migrate -t <timestamp>`
    - The timestamp is the filename, in the `db/migrations` folder. The filename should look like `20110103081132_my_cool_migration.php`, in this case `20110103081132` is the timestamp.
- Rollback the most recent migration
    - `vendor/bin/phinx rollback`
- Rollback all migrations to a specific version
    - `vendor/bin/phinx rollback -t <timestamp>`
    - Optionally you can specify a date instead of the full timestamp:
    - `vendor/bin/phinx rollback -d 20230101`

See `/db/SampleSQLMigration.php` for a simple example of creating a migration that uses raw SQL queries.
