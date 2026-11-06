<?php

namespace App\Entity;

use App\Repository\JardinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

// Cada jardín es un espacio aparte dentro de la misma instalación: sus datos
// nunca se mezclan con los de otro (ver App\Security\JardinActualResolver).
#[ORM\Entity(repositoryClass: JardinRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_JARDIN_NOMBRE', fields: ['nombre'])]
#[UniqueEntity(fields: ['nombre'], message: 'Ya existe un jardín con ese nombre.')]
class Jardin
{
    // El azul petróleo de la marca: el que usa un jardín que no eligió el suyo.
    public const COLOR_POR_DEFECTO = '#1f4d5a';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Por favor complete el campo.')]
    #[Assert\Length(max: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $direccion = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $telefono = null;

    // Color principal del jardín, en hexadecimal (#rrggbb).
    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message: 'Por favor complete el campo.')]
    #[Assert\Regex('/^#[0-9a-fA-F]{6}$/', message: 'El color tiene que tener el formato #rrggbb.')]
    private string $color = self::COLOR_POR_DEFECTO;

    // Nombre del archivo del logo, dentro de public/uploads/jardines.
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column]
    private bool $activo = true;

    /**
     * @var Collection<int, Usuario>
     */
    #[ORM\OneToMany(targetEntity: Usuario::class, mappedBy: 'jardin')]
    private Collection $usuarios;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->nombre;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = strtolower($color);

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return Collection<int, Usuario>
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }
}
