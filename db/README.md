(copied from main README.md)

We use [Phinx](https://phinx.org/) for simple migration management. The documentation on writing migrations is here:
https://book.cakephp.org/phinx/0/en/migrations.html

**Initial Setup**  
When first setting up your dev environment, run:
- `vendor/bin/phinx seed:run`
  This will populate your database with the basic data needed, except for a
  user account. You can create one from the register page and manually activate it by setting `user_verified=1` in your database.

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
