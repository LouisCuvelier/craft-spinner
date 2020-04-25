<?php
/**
 * Spinner plugin for Craft CMS 3.x
 *
 * fez
 *
 * @link      https://www.louiscuvelier.com
 * @copyright Copyright (c) 2020 Louis Cuvelier
 */

namespace louiscuvelier\spinner\services;

use craft\base\Field;
use craft\db\Connection;
use craft\db\Query;
use craft\Db\Command;
use craft\helpers\DateTimeHelper;
use louiscuvelier\spinner\fields\SpinText;
use louiscuvelier\spinner\records\SpinTexts as SpinerTextsRecord;
use louiscuvelier\spinner\Spinner;

use Craft;
use craft\base\Component;
use yii\db\Exception;

/**
 * SpinnerService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Louis Cuvelier
 * @package   Spinner
 * @since     0.0.0
 */
class SpinnerService extends Component
{
    // Constants
    // =========================================================================

    const IGNORE_DB_ATTRIBUTES = ['id', 'dateCreated', 'dateUpdated', 'uid'];

    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Spinner::$plugin->spinnerService->exampleService()
     *
     * @param $field
     * @return mixed
     */
    public function createTableLine(Field $field): bool
    {
        if (
            $field instanceof SpinText &&
            SpinerTextsRecord::findOne([
                'fieldId' => $field->id
            ]) === null
        ) {
            $record = new SpinerTextsRecord();
            $record->fieldId = $field->id;
            $record->content = '';
            $record->save();

            return true;
        }

        return false;
    }

    public function updateTableLine(Field $field): bool
    {
        $record = SpinerTextsRecord::findOne([
            'fieldId' => $field->id
        ]);
        if ($field instanceof SpinText) {
            // If is already a Spin Text
            if ($record !== null) {
                $record->setAttribute(
                    'dateUpdated',
                    DateTimeHelper::toIso8601(
                        DateTimeHelper::currentTimeStamp()
                    )
                );
                $record->save();
            } else {
                // If a field that changed to be a Spin Text
                $this->createTableLine($field);
            }

            return true;
        }

        return false;
    }

    public function deleteTableLine($field): bool
    {
        $record = SpinerTextsRecord::findOne([
            'fieldId' => $field->id
        ]);
        if ($record !== null) {
            $record->delete();
            return true;
        }

        return false;
    }

    public function getSpinTexts()
    {
        return (new Query())
            ->select([
                'spinner.id',
                'spinner.content',
                'fields.name fieldName',
                'fields.handle fieldHandle',
                'fields.id fieldId'
            ])
            ->from('{{%spinner_spintexts}} spinner')
            ->innerJoin(
                '{{%fields}} fields',
                '[[spinner.fieldId]] = [[fields.Id]]'
            )
            ->all();
    }

    public function getSpinText(int $fieldId)
    {
        return (new Query())
            ->select([
                'content',
            ])
            ->from('{{%spinner_spintexts}} spinner')
            ->where(['fieldId' => $fieldId])
            ->one();
    }

    public function saveSpinText(int $spinId, string $spinText)
    {
        $record = SpinerTextsRecord::findOne([
            'id' => $spinId
        ]);

        $record->setAttribute(
            'dateUpdated',
            DateTimeHelper::toIso8601(DateTimeHelper::currentTimeStamp())
        );
        $record->setAttribute('content', $spinText);
        $record->save();

        return true;
    }
}
