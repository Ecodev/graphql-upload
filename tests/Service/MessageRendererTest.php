<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Service;

use Ecodev\Felix\Service\MessageRenderer;
use EcodevTests\Felix\Blog\Model\User;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\TestCase;

final class MessageRendererTest extends TestCase
{
    public function testRender(): void
    {
        $user = new User();
        $email = 'foo@example.com';
        $subject = 'my subject';
        $type = 'my_type';
        $layoutParams = ['fooLayout' => 'barLayout'];
        $mailParams = ['fooMail' => 'barMail'];

        $viewRenderer = $this->createMock(RendererInterface::class);
        $viewRenderer->expects($this->exactly(2))
            ->method('render')
            ->withConsecutive(
                [
                    self::callback(function (ViewModel $viewModel) use ($user) {
                        $variables = [
                            'fooMail' => 'barMail',
                            'email' => 'foo@example.com',
                            'user' => $user,
                            'serverUrl' => 'https://example.com',
                        ];

                        return $viewModel->getTemplate() === 'my-type' &&
                            $viewModel->getVariables() === $variables;
                    }),
                ], [
                    self::callback(function (ViewModel $viewModel) use ($user) {
                        $variables = [
                            'fooLayout' => 'barLayout',
                            'content' => 'mocked-rendered-view',
                            'subject' => 'my subject',
                            'user' => $user,
                            'serverUrl' => 'https://example.com',
                            'hostname' => 'example.com',
                        ];

                        return $viewModel->getTemplate() === 'layout' &&
                            $viewModel->getVariables() === $variables;
                    }),
                ]
            )->willReturnOnConsecutiveCalls('mocked-rendered-view', 'mocked-rendered-layout');

        $messageRenderer = new MessageRenderer($viewRenderer, 'example.com');

        $messageRenderer->render($user, $email, $subject, $type, $mailParams, $layoutParams);
    }
}
