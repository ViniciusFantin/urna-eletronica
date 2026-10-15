<?php
namespace App\EventListener;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// Impede alterar o cadastro durante ou após a votação, mesmo por requisições diretas.
#[AsEventListener(event: KernelEvents::REQUEST, priority: -10)]
final class BloquearCadastrosAposAbertura
{
    public function __construct(private readonly Connection $db) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) return;
        $request = $event->getRequest();
        if (!$request->isMethod('POST')) return;
        if (!in_array($request->attributes->get('_route'), ['admin_candidatos', 'admin_registrar'], true)) return;
        $status = $this->db->fetchOne('SELECT status FROM eleicao WHERE id = 1');
        if ($status === 'ABERTA' || $status === 'ENCERRADA') {
            $event->setResponse(new Response(
                'Cadastros bloqueados após abertura da eleição. Volte ao painel.',
                Response::HTTP_CONFLICT,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            ));
        }
    }
}
