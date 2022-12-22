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
use Kumwe\CMS\Model\ContactMessagesModel;
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
class ContactMessageController extends AbstractController implements AccessInterface, CheckTokenInterface
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
     * @var  ContactMessagesModel
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * Constructor.
     *
     * @param   ContactMessagesModel                 $model  The model object.
     * @param   ContactDetailView              $view   The view object.
     * @param   Input|null                $input  The input object.
     * @param   User|null                 $user   The current user.
     * @param   AbstractApplication|null  $app    The application object.
     */
    public function __construct(
        ContactMessagesModel           $model,
        ContactDetailView        $view = null,
        Input               $input = null,
        AbstractApplication $app = null,
        User                $user = null
    ) {
        parent::__construct($input, $app);

        $this->model  = $model;
        $this->view   = $view;
        $this->user   = $user;
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
                    $this->getApplication()->enqueueMessage('Message was deleted!', 'success');
                } else {
                    $this->getApplication()->enqueueMessage('Message could not be deleted!', 'error');
                }
            } else {
                $this->getApplication()->enqueueMessage('You do not have permission to delete this message!', 'error');
            }
            // go to set page
            $this->_redirect('contactDetails');

            return true;
        }

        if ('POST' === $method) {
            $id = $this->setItem();
            if ($id > 0) {
            }
        }

        $this->_redirect('contactUs');

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
        // get the post
        $post = $this->getInput()->getInputForRequestMethod();

        // we get all the needed items
        $tempItem                     = [];
        $tempItem['id']               = $post->getInt('id', 0);
        $tempItem['email']            = $post->getString('email', '');
        $tempItem['message'] = $post->getString('message', '');

        $can_save = true;
        if (empty($tempItem['email'])) {
            $can_save = false;
        }
        if (empty($tempItem['message'])) {
            $can_save = false;
        }
        // can we save the item
        if ($can_save) {
            return $this->model->setItem(
                $tempItem['id'],
                $tempItem['email'],
                $tempItem['message'],
            );
        }

        return $tempItem['id'];
    }
}
