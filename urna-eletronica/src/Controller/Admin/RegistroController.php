<?php

namespace App\Controller\Admin;

use App\Entity\Eleitor;
use App\Repository\EleitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/registrar')]
#[IsGranted('ROLE_ADMIN')]
class RegistroController extends AbstractController
{
    #[Route('', name: 'admin_registrar', methods: ['GET', 'POST'])]
    public function registrar(
        Request $request,
        EleitorRepository $eleitorRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $dados = [
            'nome' => '',
            'tituloEleitor' => '',
            'perfil' => 'ROLE_ELEITOR',
        ];

        $erro = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid(
                'registrar_usuario',
                (string) $request->request->get('_csrf_token', '')
            )) {
                throw $this->createAccessDeniedException(
                    'Token CSRF inválido.'
                );
            }

            $dados['nome'] = trim(
                (string) $request->request->get('nome', '')
            );

            $dados['tituloEleitor'] = trim(
                (string) $request->request->get('tituloEleitor', '')
            );

            $dados['perfil'] = (string) $request->request->get(
                'perfil',
                'ROLE_ELEITOR'
            );

            $senha = (string) $request->request->get('senha', '');
            $confirmarSenha = (string) $request->request->get(
                'confirmarSenha',
                ''
            );

            if (
                $dados['nome'] === '' ||
                mb_strlen($dados['nome']) > 150
            ) {
                $erro = 'Informe um nome válido de até 150 caracteres.';
            } elseif (
                !preg_match('/^[0-9]{12}$/', $dados['tituloEleitor'])
            ) {
                $erro = 'O título fictício deve conter exatamente 12 números.';
            } elseif (
                !in_array(
                    $dados['perfil'],
                    ['ROLE_ELEITOR', 'ROLE_ADMIN'],
                    true
                )
            ) {
                $erro = 'Perfil de usuário inválido.';
            } elseif (strlen($senha) < 12) {
                $erro = 'A senha deve conter pelo menos 12 caracteres.';
            } elseif ($senha !== $confirmarSenha) {
                $erro = 'As senhas informadas não coincidem.';
            } elseif (
                $eleitorRepository->findOneBy([
                    'tituloEleitor' => $dados['tituloEleitor'],
                ])
            ) {
                $erro = 'Este título de eleitor já está cadastrado.';
            } else {
                $eleitor = new Eleitor();

                $eleitor->setNome($dados['nome']);
                $eleitor->setTituloEleitor($dados['tituloEleitor']);
                $eleitor->setRoles([$dados['perfil']]);
                $eleitor->setHasVoted(false);
                $eleitor->setCreatedAt(new \DateTimeImmutable());

                $hash = $passwordHasher->hashPassword(
                    $eleitor,
                    $senha
                );

                $eleitor->setSenha($hash);

                $entityManager->persist($eleitor);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Usuário cadastrado com sucesso!'
                );

                return $this->redirectToRoute('admin_registrar');
            }
        }

        return $this->render('admin/registrar.html.twig', [
            'erro' => $erro,
            'dados' => $dados,
        ]);
    }
}