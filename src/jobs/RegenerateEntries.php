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
use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\db\Table;
use craft\elements\Entry;
use craft\helpers\Db;
use craft\queue\BaseJob;
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
    public function execute($queue): void
    {
        $entryTypes = (new Query())
            ->select(['entrytypes.handle'])
            ->from(['{{%entrytypes}} entrytypes'])
            ->innerJoin('{{%fieldlayoutfields}} fieldlayoutfields', '[[entrytypes.fieldLayoutId]] = [[fieldlayoutfields.layoutId]]')
            ->where(Db::parseParam('fieldlayoutfields.fieldId', $this->fieldId))
            ->column();

        $entriesQuery = Entry::find()->type($entryTypes);

        $totalElements = $entriesQuery->count();
        $currentElement = 0;

        $elements = Craft::$app->getElements();

        try {
            foreach ($entriesQuery->each() as $entry) {
                $entry = Entry::find()->id($entry->id)->section($entry->section->handle)->one();
                $this->setProgress($queue, $currentElement++ / $totalElements);

                $spintax = new Spintax();
                $newContent = $spintax->process($this->spinText);
                $entry->setFieldValues([$this->fieldHandle => $newContent]);

                if (!$elements->saveElement($entry, true)) {
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
