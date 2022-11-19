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

use louiscuvelier\spinner\jobs\RegenerateEntries;

use Craft;
use craft\web\Controller;
use louiscuvelier\spinner\Spinner;

/**
 * Cp Controller
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
class CpController extends Controller
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
     * e.g.: actions/spinner/cp
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $this->requireCpRequest();

        $spinTexts = Spinner::$plugin->spinnerService->getSpinTexts();

        $variables = [];
        $variables["spinTexts"] = $spinTexts;
        return $this->renderTemplate('spinner', $variables);
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/spinner/cp/save-spin
     *
     * @return mixed
     */
    public function actionSaveSpin()
    {
        $this->requirePostRequest();
        $this->requireCpRequest();

        $request = Craft::$app->getRequest();

        $spinText = (string)$request->getParam('spinText', '');
        $spinId = (int)$request->getParam('spinId');
        $fieldName = (string)$request->getParam('fieldName', '');
        $fieldHandle = (string)$request->getParam('fieldHandle');
        $fieldId = (int)$request->getParam('fieldId');

        if ($spinId && trim($fieldName) && trim($fieldHandle) && $fieldId) {
            Spinner::$plugin->spinnerService->saveSpinText($spinId, $spinText);

            if ($request->getParam('saveAndGenerate') !== null) {

                Craft::$app->getQueue()->push(
                    new RegenerateEntries([
                        'fieldId' => $fieldId,
                        'fieldHandle' => $fieldHandle,
                        'spinText' => $spinText
                    ])
                );

                Craft::$app->getSession()->setNotice(
                    Craft::t(
                        'spinner',
                        'Spin for "{fieldName}" saved. All spin texts are being generated.',
                        [
                            'fieldName' => $fieldName
                        ]
                    )
                );

                return $this->refresh();
            }
            Craft::$app->getSession()->setNotice(
                Craft::t('spinner', 'Spin for "{fieldName}" saved.', [
                    'fieldName' => $fieldName
                ])
            );

            return $this->refresh();
        }

        return Craft::$app->getSession()->setError(
            Craft::t('spinner', 'Spin for "{fieldName}" can\'t be saved.', [
                'fieldName' => $fieldName
            ])
        );
    }
}
