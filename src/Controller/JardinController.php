<?php

namespace App\Controller;

use App\Entity\Jardin;
use App\Form\Type\JardinType;
use App\Repository\JardinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Los jardines los da de alta el super administrador: es "armar el espacio de
// tu jardín" del brochure, con su logo y sus colores.
#[Route('/jardin')]
final class JardinController extends AbstractController
{
    public function __construct(
        #[Autowire('%app.directorio_logos%')] private readonly string $directorioLogos,
    ) {
    }

    #[Route(name: 'app_jardin_index', methods: ['GET'])]
    public function index(JardinRepository $jardinRepository): Response
    {
        return $this->render('jardin/index.html.twig', [
            'jardines' => $jardinRepository->findBy([], ['nombre' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_jardin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jardin = new Jardin();
        $form = $this->createForm(JardinType::class, $jardin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->guardarLogo($form, $jardin);
            $entityManager->persist($jardin);
            $entityManager->flush();

            $this->addFlash('success', 'El jardín "' . $jardin . '" se creó correctamente.');

            return $this->redirectToRoute('app_jardin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jardin/form.html.twig', [
            'jardin' => $jardin,
            'form' => $form,
            'titulo' => 'Nuevo jardín',
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_jardin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Jardin $jardin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JardinType::class, $jardin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->guardarLogo($form, $jardin);
            $entityManager->flush();

            $this->addFlash('success', 'El jardín "' . $jardin . '" se modificó correctamente.');

            return $this->redirectToRoute('app_jardin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jardin/form.html.twig', [
            'jardin' => $jardin,
            'form' => $form,
            'titulo' => 'Editar jardín',
        ]);
    }

    // Un logo nuevo reemplaza al anterior, que se borra del disco.
    private function guardarLogo(FormInterface $form, Jardin $jardin): void
    {
        /** @var UploadedFile|null $archivo */
        $archivo = $form->get('archivoLogo')->getData();

        if (null === $archivo) {
            return;
        }

        $nombre = bin2hex(random_bytes(8)) . '.' . ($archivo->guessExtension() ?? 'png');
        $archivo->move($this->directorioLogos, $nombre);

        if (null !== $jardin->getLogo() && is_file($this->directorioLogos . '/' . $jardin->getLogo())) {
            unlink($this->directorioLogos . '/' . $jardin->getLogo());
        }

        $jardin->setLogo($nombre);
    }
}
