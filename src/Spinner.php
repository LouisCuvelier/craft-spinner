<?php
/**
 * Spinner plugin for Craft CMS 3.x
 *
 * Content spinning some text
 *
 * @link      https://www.louiscuvelier.com
 * @copyright Copyright (c) 2020 Louis Cuvelier
 */

namespace louiscuvelier\spinner;

use louiscuvelier\spinner\records\SpinTexts as SpinerTextsRecord;
use louiscuvelier\spinner\services\SpinnerService as SpinnerService;
use louiscuvelier\spinner\fields\SpinText as SpinTextField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\FieldEvent;

use yii\base\Event;
use yii\helpers\VarDumper;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Louis Cuvelier
 * @package   Spinner
 * @since     0.0.0
 *
 * @property  SpinnerService $spinnerService
 */
class Spinner extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Spinner::$plugin
     *
     * @var Spinner
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Spinner::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        self::$plugin->setComponents([
            'spinnerService' =>
                \louiscuvelier\spinner\services\SpinnerService::class
        ]);

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'spinner/generate-text' => 'spinner/generate-text',
                    'spinner/cp/save-spin' => 'spinner/cp/save-spin',
                    'spinner' => 'spinner/cp/index'
                ]);
            }
        );

        // Register our fields
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (
            RegisterComponentTypesEvent $event
        ) {
            $event->types[] = SpinTextField::class;
        });

        // Register on save field event
        Event::on(Fields::class, Fields::EVENT_AFTER_SAVE_FIELD, function (
            FieldEvent $event
        ) {
            if ($event->isNew) {
                self::$plugin->spinnerService->createTableLine($event->field);
            } else {
                self::$plugin->spinnerService->updateTableLine($event->field);
            }
        });

        // Register on delete field event
        Event::on(Fields::class, Fields::EVENT_AFTER_DELETE_FIELD, function (
            FieldEvent $event
        ) {
            self::$plugin->spinnerService->deleteTableLine($event->field);
        });

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t('spinner', '{name} plugin loaded', [
                'name' => $this->name
            ]),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
}
