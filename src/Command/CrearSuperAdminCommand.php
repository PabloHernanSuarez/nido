<?php

namespace App\Command;

use App\Controller\UsuarioController;
use App\Entity\Usuario;
use App\Security\RoleHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// El primer usuario de una instalación nueva: con él se dan de alta los
// jardines y sus directoras.
#[AsCommand(
    name: 'app:usuario:crear-super-admin',
    description: 'Crea un super administrador y muestra su contraseña inicial.',
)]
class CrearSuperAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Mail con el que entra.')
            ->addArgument('nombre', InputArgument::REQUIRED, 'Nombre.')
            ->addArgument('apellido', InputArgument::REQUIRED, 'Apellido.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $usuario = (new Usuario())
            ->setEmail($input->getArgument('email'))
            ->setNombre($input->getArgument('nombre'))
            ->setApellido($input->getArgument('apellido'))
            ->setPerfil(RoleHelper::SUPER_ADMIN);

        $errores = $this->validator->validate($usuario);
        if (count($errores) > 0) {
            foreach ($errores as $error) {
                $io->error($error->getPropertyPath() . ': ' . $error->getMessage());
            }

            return Command::FAILURE;
        }

        $password = UsuarioController::generarPasswordInicial();
        $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $password));

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        $io->success(sprintf('Se creó el super administrador %s. Contraseña inicial: %s', $usuario->getEmail(), $password));

        return Command::SUCCESS;
    }
}
