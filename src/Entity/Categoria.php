<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
#[ORM\Table(name: 'categoria', schema: 'apodnasa')]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $imagen = null;

    #[ORM\ManyToMany(targetEntity: FotoAstral::class, inversedBy: 'categorias')]
    #[ORM\JoinTable(name: 'categoria_foto')]
    #[ORM\JoinColumn(name: 'id_categoria', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'id_foto_astral', referencedColumnName: 'id')]
    private Collection $fotoAstrals;

    public function __construct()
    {
        $this->fotoAstrals = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): static { $this->nombre = $nombre; return $this; }

    public function getImagen(): ?string { return $this->imagen; }
    public function setImagen(string $imagen): static { $this->imagen = $imagen; return $this; }

    /** @return Collection<int, FotoAstral> */
    public function getFotoAstrals(): Collection { return $this->fotoAstrals; }

    public function addFotoAstral(FotoAstral $fotoAstral): static
    {
        if (!$this->fotoAstrals->contains($fotoAstral)) {
            $this->fotoAstrals->add($fotoAstral);
            $fotoAstral->addCategoria($this);
        }
        return $this;
    }

    public function removeFotoAstral(FotoAstral $fotoAstral): static
    {
        if ($this->fotoAstrals->removeElement($fotoAstral)) {
            $fotoAstral->removeCategoria($this);
        }
        return $this;
    }
}
