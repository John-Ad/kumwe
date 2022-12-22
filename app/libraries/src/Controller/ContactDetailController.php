<?php

/**
 * @package    Kumwe CMS
 *
 * @created    21st December 2022
 * @author     John Adriaans
 * @git        Kumwe CMS <https://git.vdm.dev/Kumwe/cms>
 * @license    GNU General Public License version 2; see LICENSE.txt
 */

namespace Kumwe\CMS\Controller;

use Exception;
use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use Joomla\Authentication\Password\BCryptHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Kumwe\CMS\Controller\Util\AccessInterface;
use Kumwe\CMS\Controller\Util\AccessTrait;
use Kumwe\CMS\Controller\Util\CheckTokenInterface;
use Kumwe\CMS\Controller\Util\CheckTokenTrait;
use Kumwe\CMS\Date\Date;
use Kumwe\CMS\Factory;
use Kumwe\CMS\Model\ContactDetailsModel;
use Kumwe\CMS\Model\UserModel;
use Kumwe\CMS\User\User;
use Kumwe\CMS\User\UserFactoryInterface;
use Kumwe\CMS\View\Admin\ContactDetailView;
use Kumwe\CMS\View\Admin\UserHtmlView;

/**
 * Controller handling the requests
 *
 * @method         \Kumwe\CMS\Application\AdminApplication  getApplication()  Get the application object.
 * @property-read  \Kumwe\CMS\Application\AdminApplication $app              Application object
 */
class ContactDetailController extends AbstractController implements AccessInterface, CheckTokenInterface
{
    use AccessTrait, CheckTokenTrait;

    /**
     * The view object.
     *
     * @var  UserHtmlView
     */
    private $view;

    /**
     * The model object.
     *
     * @var  ContactDetailsModel
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * Constructor.
     *
     * @param   ContactDetailsModel                 $model  The model object.
     * @param   ContactDetailView              $view   The view object.
     * @param   Input|null                $input  The input object.
     * @param   User|null                 $user   The current user.
     * @param   AbstractApplication|null  $app    The application object.
     */
    public function __construct(
        ContactDetailsModel           $model,
        ContactDetailView        $view,
        Input               $input = null,
        AbstractApplication $app = null,
        User                $user = null
    ) {
        parent::__construct($input, $app);

        $this->model  = $model;
        $this->view   = $view;
        $this->user   = ($user) ?: Factory::getContainer()->get(UserFactoryInterface::class)->getUser();
    }

    /**
     * Execute the controller.
     *
     * @return  boolean
     * @throws Exception
     */
    public function execute(): bool
    {
        // Do not Enable browser caching
        $this->getApplication()->allowCache(false);

        $method = $this->getInput()->getMethod();
        $task   = $this->getInput()->getString('task', '');
        $id     = $this->getInput()->getInt('id', 0);

        // if task is delete
        if ('delete' === $task) {

            // check that user is authorized and can performa a delete
            if ($this->allow('contactDetails') && $this->user->get('access.contactdetails.delete', false)) {

                if ($this->model->delete($id)) {
                    $this->getApplication()->enqueueMessage('Contact detail was deleted!', 'success');
                } else {
                    $this->getApplication()->enqueueMessage('Contact detail could not be deleted!', 'error');
                }
            } else {
                $this->getApplication()->enqueueMessage('You do not have permission to delete this contact detail!', 'error');
            }
            // go to set page
            $this->_redirect('contactDetails');

            return true;
        }

        // set the current user ID
        $user_id = $this->user->get('id', -1);

        if ('POST' === $method) {
            // check permissions
            $update = ($id > 0 && $this->user->get('access.contactdetails.update', false));
            $create = ($id == 0 && $this->user->get('access.contactdetails.create', false));

            if ($create || $update) {
                $id = $this->setItem();
            } else {
                // not allowed creating item
                if ($id == 0) {
                    $this->getApplication()->enqueueMessage('You do not have permission to create contact details!', 'error');
                }
                // not allowed updating item
                if ($id > 0) {
                    $this->getApplication()->enqueueMessage('You do not have permission to update contact details!', 'error');
                }
            }
        }

        // check permissions
        $read       = ($id > 0 && $this->user->get('access.contactdetails.read', false));
        $create     = ($id == 0 && $this->user->get('access.contactdetails.create', false));
        $selfUpdate = ($id > 0 && $id == $user_id);

        // check if user is allowed to access
        if ($this->allow('contactDetails') && ($read || $create || $selfUpdate)) {
            // set values for view
            $this->view->setActiveId($id);
            $this->view->setActiveView('contactDetail');

            $this->getApplication()->setResponse(new HtmlResponse($this->view->render()));
        } else {
            // not allowed creating user
            if ($id == 0 && !$create) {
                $this->getApplication()->enqueueMessage('You do not have permission to create contact details!', 'error');
            }
            // not allowed updating user
            if ($id > 0 && !$read) {
                $this->getApplication()->enqueueMessage('You do not have permission to read contact details!', 'error');
            }

            // go to set page
            $this->_redirect('contactDetails');
        }

        return true;
    }

    /**
     * Set an item
     *
     *
     * @return  int
     * @throws \Exception
     */
    protected function setItem(): int
    {
        // always check the post token
        $this->checkToken();
        // get the post
        $post = $this->getInput()->getInputForRequestMethod();

        // we get all the needed items
        $tempItem                     = [];
        $tempItem['id']               = $post->getInt('id', 0);
        $tempItem['typeId']            = $post->getInt('typeId', 0);
        $tempItem['value'] = $post->getString('value', '');

        $can_save = true;
        if (empty($tempItem['typeId'])) {
            // we show a warning message
            $this->getApplication()->enqueueMessage('A type is required.', 'error');
            $can_save = false;
        }
        if ($tempItem['typeId'] <= 0 || $tempItem['typeId'] > 3) {
            $this->getApplication()->enqueueMessage('Invalid type was chosen!', 'error');
            $can_save = false;
        }

        if (empty($tempItem['value'])) {
            // we show a warning message
            $this->getApplication()->enqueueMessage('A value is required.', 'error');
            $can_save = false;
        }
        // can we save the item
        if ($can_save) {
            return $this->model->setItem(
                $tempItem['id'],
                $tempItem['typeId'],
                $tempItem['value'],
            );
        }

        return $tempItem['id'];
    }
}
