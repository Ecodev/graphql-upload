<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Doctrine\ORM\EntityManager;
use Ecodev\Felix\Model\User;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

/**
 * Service to queue new message for pre-defined purposes
 */
class MessageQueuer
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var RendererInterface
     */
    private $viewRenderer;

    public function __construct(EntityManager $entityManager, RendererInterface $viewRenderer, string $hostname)
    {
        $this->entityManager = $entityManager;
        $this->hostname = $hostname;
        $this->viewRenderer = $viewRenderer;
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
    protected function renderMessage(?User $user, string $email, string $subject, string $type, array $mailParams): string
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
        $layoutModel = new ViewModel([$model->captureTo() => $partialContent]);
        $layoutModel->setTemplate('layout');
        $layoutModel->setVariable('subject', $subject);
        $layoutModel->setVariable('user', $user);
        $layoutModel->setVariable('serverUrl', $serverUrl);
        $layoutModel->setVariable('hostname', $this->hostname);
        $content = $this->viewRenderer->render($layoutModel);

        return $content;
    }
}
