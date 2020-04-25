<?php
/**
 * Spinner plugin for Craft CMS 3.x
 *
 * fez
 *
 * @link      https://www.louiscuvelier.com
 * @copyright Copyright (c) 2020 Louis Cuvelier
 */

namespace louiscuvelier\spinner\migrations;

use craft\db\Query;
use louiscuvelier\spinner\fields\SpinText as SpinTextField;
use louiscuvelier\spinner\Spinner;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Spinner Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Louis Cuvelier
 * @package   Spinner
 * @since     0.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // spinner_spintexts table
        $tableSchema = Craft::$app->db->schema->getTableSchema(
            '{{%spinner_spintexts}}'
        );
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable('{{%spinner_spintexts}}', [
                'id' => $this->primaryKey(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                // Custom columns in the table
                'fieldId' => $this->integer()->notNull(),
                'content' => $this->text()->notNull()
            ]);
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        // spinner_spintexts table
        //        $this->createIndex(
        //            $this->db->getIndexName(
        //                '{{%spinner_spintexts}}',
        //                'content',
        //                true
        //            ),
        //            '{{%spinner_spintexts}}',
        //            'content',
        //            true
        //        );
        // Additional commands depending on the db driver
        //        switch ($this->driver) {
        //            case DbConfig::DRIVER_MYSQL:
        //                break;
        //            case DbConfig::DRIVER_PGSQL:
        //                break;
        //        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        // spinner_spintexts table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%spinner_spintexts}}', 'fieldId'),
            '{{%spinner_spintexts}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData(): void
    {
        // Create database lines if Spin Text fields already exists from a previous installation
        Spinner::$plugin->spinnerService->createDefaultLines();
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables(): void
    {
        // spinner_spintexts table
        $this->dropTableIfExists('{{%spinner_spintexts}}');
    }
}
