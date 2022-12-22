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

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use Laminas\Diactoros\Response\HtmlResponse;
use Kumwe\CMS\Controller\Util\AccessInterface;
use Kumwe\CMS\Controller\Util\AccessTrait;
use Kumwe\CMS\Controller\Util\CheckTokenInterface;
use Kumwe\CMS\Controller\Util\CheckTokenTrait;
use Kumwe\CMS\Factory;
use Kumwe\CMS\User\User;
use Kumwe\CMS\User\UserFactoryInterface;
use Kumwe\CMS\View\Admin\ContactDetailsView;
use Kumwe\CMS\View\Admin\MenusHtmlView;
use Kumwe\CMS\View\Site\ContactUsPageView;

/**
 * Controller handling the requests
 *
 * @method         \Kumwe\CMS\Application\AdminApplication  getApplication()  Get the application object.
 * @property-read  \Kumwe\CMS\Application\AdminApplication $app              Application object
 */
class ContactUsPageController extends AbstractController implements AccessInterface, CheckTokenInterface
{
    use AccessTrait, CheckTokenTrait;

    /**
     * The view object.
     *
     * @var  ContactUsPageView
     */
    private $view;


    /**
     * Constructor.
     *
     * @param   ContactUsPageView             $view   The view object.
     * @param   Input|null                $input  The input object.
     * @param   AbstractApplication|null  $app    The application object.
     */
    public function __construct(
        ContactUsPageView  $view,
        Input               $input = null,
        AbstractApplication $app = null
    ) {
        parent::__construct($input, $app);

        $this->view = $view;
    }

    /**
     * Execute the controller.
     *
     * @return  boolean
     * @throws \Exception
     */
    public function execute(): bool
    {
        // Do not Enable browser caching
        $this->getApplication()->allowCache(false);

        $this->view->setActiveView('contactUs');

        $this->getApplication()->setResponse(new HtmlResponse($this->view->render()));

        return true;
    }
}
