<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Ecodev\Felix\Model\User;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

/**
 * Service to render message to HTML
 */
final class MessageRenderer
{
    /**
     * @var RendererInterface
     */
    private $viewRenderer;

    /**
     * @var string
     */
    private $hostname;

    public function __construct(RendererInterface $viewRenderer, string $hostname)
    {
        $this->viewRenderer = $viewRenderer;
        $this->hostname = $hostname;
    }

    /**
     * Render a message by templating
     *
     * @param null|User $user
     * @param string $email
     * @param string $subject
     * @param string $type
     * @param array $mailParams
     *
     * @return string
     */
    public function render(?User $user, string $email, string $subject, string $type, array $mailParams, array $layoutParams = []): string
    {
        // First render the view
        $serverUrl = 'https://' . $this->hostname;
        $model = new ViewModel($mailParams);
        $model->setTemplate(str_replace('_', '-', $type));
        $model->setVariable('email', $email);
        $model->setVariable('user', $user);
        $model->setVariable('serverUrl', $serverUrl);
        $partialContent = $this->viewRenderer->render($model);

        // Then inject it into layout
        $layoutModel = new ViewModel($layoutParams);
        $layoutModel->setTemplate('layout');
        $layoutModel->setVariable($model->captureTo(), $partialContent);
        $layoutModel->setVariable('subject', $subject);
        $layoutModel->setVariable('user', $user);
        $layoutModel->setVariable('serverUrl', $serverUrl);
        $layoutModel->setVariable('hostname', $this->hostname);
        $content = $this->viewRenderer->render($layoutModel);

        return $content;
    }
}
