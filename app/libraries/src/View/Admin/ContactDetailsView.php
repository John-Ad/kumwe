<?php

/**
 * @package    Kumwe CMS
 *
 * @created    21st December 2022
 * @author     John Adriaans
 * @git        Kumwe CMS <https://git.vdm.dev/Kumwe/cms>
 * @license    GNU General Public License version 2; see LICENSE.txt
 */

namespace Kumwe\CMS\View\Admin;

use Kumwe\CMS\Model\ContactDetailsModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\HtmlView;
use Kumwe\CMS\Model\ContactMessagesModel;

/**
 * HTML view class for the application
 */
class ContactDetailsView extends HtmlView
{
    /**
     * The model objects.
     *
     * @var  ContactDetailsModel
     * @var  ContactMessagesModel
     */
    private $model;
    private $contactMessagesModel;

    /**
     * Instantiate the view.
     *
     * @param   ContactDetailsModel         $model       The model object.
     * @param   ContactMessagesModel         $contactMessagesModel        model object.
     * @param   RendererInterface  $renderer    The renderer object.
     */
    public function __construct(ContactDetailsModel $model, ContactMessagesModel $contactMessagesModel, RendererInterface $renderer)
    {
        parent::__construct($renderer);

        $this->model = $model;
        $this->contactMessagesModel = $contactMessagesModel;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render(): string
    {
        $this->setData([
            'list' => $this->model->getItems(),
            'contactMessages' => $this->contactMessagesModel->getItems()
        ]);
        return parent::render();
    }

    /**
     * Set the active view
     *
     * @param   string  $name  The active view name
     *
     * @return  void
     */
    public function setActiveView(string $name): void
    {
        $this->setLayout($this->model->setLayout($name));
    }
}
