<?php
/**
 * Spinner plugin for Craft CMS 3.x
 *
 * Content spinning some text
 *
 * @link      https://www.louiscuvelier.com
 * @copyright Copyright (c) 2020 Louis Cuvelier
 */

namespace louiscuvelier\spinner\controllers;

use craft\db\Query;
use louiscuvelier\spinner\helpers\Spintax;
use louiscuvelier\spinner\Spinner;

use Craft;
use craft\web\Controller;

/**
 * GenerateText Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Louis Cuvelier
 * @package   Spinner
 * @since     0.0.0
 */
class GenerateTextController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/spinner/generate-text
     *
     * @return mixed
     */
    public function actionIndex($fieldName)
    {
        $this->requireCpRequest();

        $fieldData = Craft::$app->fields->getFieldByHandle($fieldName);
        $fieldId = $fieldData['id'];

        $spinText = Spinner::$plugin->spinnerService->getSpinText($fieldId);
        $spintax = new Spintax();

        return $spintax->process($spinText['content']);
    }
}
