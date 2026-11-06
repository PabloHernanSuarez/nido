<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use App\Security\RoleHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

// Todos entran con su mail: el equipo del jardín y las familias. El perfil
// (RoleHelper::PERFILES) decide qué ve cada uno.
#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USUARIO_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'El mail ya está en uso.')]
#[Assert\Callback('validarJardin')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Por favor complete el campo.')]
    #[Assert\Email(message: 'El mail no es válido.')]
    #[Assert\Length(max: 100)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Por favor seleccione un perfil.')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Por favor complete el campo.')]
    #[Assert\Length(max: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Por favor complete el campo.')]
    #[Assert\Length(max: 50)]
    private ?string $apellido = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordChangedAt = null;

    // Todos pertenecen a un jardín salvo el super administrador.
    #[ORM\ManyToOne(inversedBy: 'usuarios')]
    private ?Jardin $jardin = null;

    public function __toString(): string
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    // El perfil es el único rol guardado (Directora, Docente, Familia...).
    public function getPerfil(): ?string
    {
        return $this->roles[0] ?? null;
    }

    public function setPerfil(?string $perfil): static
    {
        $this->roles = null === $perfil ? [] : [$perfil];

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', (string) $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPasswordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?\DateTimeImmutable $passwordChangedAt): static
    {
        $this->passwordChangedAt = $passwordChangedAt;

        return $this;
    }

    public function getJardin(): ?Jardin
    {
        return $this->jardin;
    }

    public function setJardin(?Jardin $jardin): static
    {
        $this->jardin = $jardin;

        return $this;
    }

    public function validarJardin(ExecutionContextInterface $context): void
    {
        $perfil = $this->getPerfil();

        if (null === $perfil) {
            return;
        }

        if (RoleHelper::esDeJardin($perfil) && null === $this->jardin) {
            $context->buildViolation('El perfil seleccionado tiene que tener un jardín.')
                ->atPath('jardin')
                ->addViolation();
        }

        if (!RoleHelper::esDeJardin($perfil) && null !== $this->jardin) {
            $context->buildViolation('El perfil seleccionado no puede tener un jardín.')
                ->atPath('jardin')
                ->addViolation();
        }
    }
}
