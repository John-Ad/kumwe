<?php

/**
 * @package    Kumwe CMS
 *
 * @created    21st December 2022
 * @author     John Adriaans
 * @git        Kumwe CMS <https://git.vdm.dev/Kumwe/cms>
 * @license    GNU General Public License version 2; see LICENSE.txt
 */

namespace Kumwe\CMS\View\Site;

use Kumwe\CMS\Model\ContactDetailsModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\HtmlView;

/**
 * HTML view class for the application
 */
class ContactUsPageView extends HtmlView
{
    /**
     * The model object.
     *
     * @var  ContactDetailsModel
     */
    private $model;

    /**
     * Instantiate the view.
     *
     * @param   ContactDetailsModel         $model       The model object.
     * @param   RendererInterface  $renderer    The renderer object.
     */
    public function __construct(ContactDetailsModel $model, RendererInterface $renderer)
    {
        parent::__construct($renderer);

        $this->model = $model;
    }

    /**
     * Method to render the view
     *
     * @return  string  The rendered view
     */
    public function render(): string
    {
        $this->setData(['contactDetails' => $this->model->getItems()]);
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
