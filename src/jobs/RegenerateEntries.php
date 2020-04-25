<?php
/**
 * Spinner plugin for Craft CMS 3.x
 *
 * ezrgh
 *
 * @link      https://www.louiscuvelier.com
 * @copyright Copyright (c) 2020 Louis Cuvelier
 */

namespace louiscuvelier\spinner\jobs;

use Craft;
use craft\db\QueryAbortedException;
use craft\elements\Entry;
use craft\helpers\App;
use craft\queue\BaseJob;
use craft\records\EntryType;
use craft\records\FieldLayoutField;
use louiscuvelier\spinner\helpers\Spintax;
use yii\base\Exception;

/**
 * @author    Louis Cuvelier
 * @package   Spinner
 * @since     0.0.0
 */
class RegenerateEntries extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $fieldId;

    /**
     * @var string
     */
    public $spinText;

    /**
     * @var string
     */
    public $fieldHandle;

    // Public Methods
    // =========================================================================
    public function execute($queue)
    {
        $layoutQuery = FieldLayoutField::find()->where(['=', 'fieldId', $this->fieldId]);
        $layoutIds = [];
        foreach ($layoutQuery->each() as $layout) {
            $layoutIds[] = $layout['layoutId'];
        }

        $entryTypeQuery = EntryType::find()->where(['in', 'fieldLayoutId', $layoutIds]);
        $entryTypeIds = [];
        foreach ($entryTypeQuery->each() as $entryType) {
            $entryTypeIds[] = $entryType['id'];
        }
        $entryQuery = Entry::find()->where(['in', 'elements.fieldLayoutId', $entryTypeIds]);

        $totalElements = $entryQuery->count();
        $currentElement = 0;

        $elements = Craft::$app->getElements();

        try {
            foreach ($entryQuery->each() as $entry) {
                $this->setProgress($queue, $currentElement++ / $totalElements);

                $spintax = new Spintax();
                $newContent = $spintax->process($this->spinText);
                $entry->setFieldValues([$this->fieldHandle => $newContent]);

                if (!$elements->saveElement($entry)) {
                    throw new Exception(
                        'Couldn’t save element ' .
                        $entry->id .
                        ' (' .
                        get_class($entry) .
                        ') due to validation errors.'
                    );
                }
            }
        } catch (QueryAbortedException $e) {
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t(
            'spinner',
            'Generation of all spin texts in the field "{fieldHandle}"',
            [
                'fieldHandle' => $this->fieldHandle
            ]
        );
    }
}
