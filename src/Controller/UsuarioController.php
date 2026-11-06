<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\UsuarioType;
use App\Repository\UsuarioRepository;
use App\Security\JardinActualResolver;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/usuario')]
final class UsuarioController extends AbstractController
{
    public function __construct(
        private readonly JardinActualResolver $jardinActual,
    ) {
    }

    #[Route(name: 'app_usuario_index', methods: ['GET'])]
    public function index(Request $request, UsuarioRepository $usuarioRepository, PaginatorInterface $paginator): Response
    {
        $qb = $usuarioRepository->createQueryBuilderVisiblesPara($this->jardinActual->getJardin());

        $busqueda = trim((string) $request->query->get('q'));
        if ('' !== $busqueda) {
            $qb->andWhere('u.nombre LIKE :q OR u.apellido LIKE :q OR u.email LIKE :q')
                ->setParameter('q', '%' . $busqueda . '%');
        }

        return $this->render('usuario/index.html.twig', [
            'pagination' => $paginator->paginate($qb, $request->query->getInt('page', 1), 20),
            'busqueda' => $busqueda,
        ]);
    }

    // La contraseña inicial la genera el sistema y se muestra una sola vez:
    // en el primer ingreso el usuario la tiene que cambiar
    // (App\EventSubscriber\PasswordChangeSubscriber).
    #[Route('/new', name: 'app_usuario_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = new Usuario();
        $jardin = $this->jardinActual->getJardin();
        $usuario->setJardin($jardin);

        $form = $this->createForm(UsuarioType::class, $usuario, ['jardin' => $jardin]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordInicial = self::generarPasswordInicial();
            $usuario->setPassword($passwordHasher->hashPassword($usuario, $passwordInicial));

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Se creó el usuario de %s. Su contraseña inicial es %s: pasásela, porque no se vuelve a mostrar. La va a cambiar al entrar por primera vez.',
                $usuario,
                $passwordInicial
            ));

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/form.html.twig', [
            'form' => $form,
            'titulo' => 'Nuevo usuario',
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_usuario_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Usuario $usuario, EntityManagerInterface $entityManager): Response
    {
        $this->verificarJardin($usuario);

        $form = $this->createForm(UsuarioType::class, $usuario, ['jardin' => $this->jardinActual->getJardin()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'El usuario de ' . $usuario . ' se modificó correctamente.');

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/form.html.twig', [
            'form' => $form,
            'titulo' => 'Editar usuario',
        ]);
    }

    #[Route('/{id<\d+>}/habilitar', name: 'app_usuario_habilitar', methods: ['POST'])]
    public function habilitar(Request $request, Usuario $usuario, EntityManagerInterface $entityManager): Response
    {
        $this->verificarJardin($usuario);

        if (!$this->isCsrfTokenValid('habilitar' . $usuario->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($usuario === $this->getUser()) {
            $this->addFlash('danger', 'No podés deshabilitar tu propio usuario.');

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        $usuario->setIsActive(!$usuario->isActive());
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'El usuario de %s se %s correctamente.',
            $usuario,
            $usuario->isActive() ? 'habilitó' : 'deshabilitó'
        ));

        return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cambiar-password', name: 'app_usuario_cambiar_password', methods: ['GET', 'POST'])]
    public function cambiarPassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // En el cambio obligatorio del primer ingreso no se pide la contraseña
        // actual: el usuario la acaba de tipear para entrar.
        $form = $this->createForm(ChangePasswordType::class, null, [
            'pedir_password_actual' => null !== $usuario->getPasswordChangedAt(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $usuario->setPassword($passwordHasher->hashPassword($usuario, $form->get('plainPassword')->getData()));
            $usuario->setPasswordChangedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Cambiaste tu contraseña.');

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('usuario/cambiar_password.html.twig', [
            'form' => $form,
            'primerIngreso' => null === $usuario->getPasswordChangedAt(),
        ]);
    }

    // Un usuario de otro jardín, para la directora, no existe.
    private function verificarJardin(Usuario $usuario): void
    {
        if (!$this->jardinActual->puedeVer($usuario->getJardin())) {
            throw $this->createNotFoundException();
        }
    }

    // Fácil de dictar: sin letras que se confunden (l, I, O, 0).
    public static function generarPasswordInicial(): string
    {
        $letras = 'abcdefghjkmnpqrstuvwxyz';
        $password = strtoupper($letras[random_int(0, strlen($letras) - 1)]);

        for ($i = 0; $i < 5; $i++) {
            $password .= $letras[random_int(0, strlen($letras) - 1)];
        }

        return $password . random_int(10, 99);
    }
}
