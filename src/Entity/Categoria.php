<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
#[ORM\Table(name: 'categoria', schema: 'apodnasa')]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "nombre" ,length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(name: "imagen" ,type: Types::TEXT)]
    private ?string $imagen = null;

    /**
     * @var Collection<int, Ranking>
     */
    #[ORM\OneToMany(targetEntity: Ranking::class, mappedBy: 'categoria')]
    private Collection $rankings;

    /**
     * @var Collection<int, FotoAstral>
     */
    #[JoinTable(name: 'categoria_foto')]
    #[JoinColumn(name: 'id_categoria', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'id_foto_astral', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: FotoAstral::class)]
    private Collection $fotoAstrals;

    public function __construct()
    {
        $this->rankings = new ArrayCollection();
        $this->fotoAstrals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(string $imagen): static
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * @return Collection<int, Ranking>
     */
    public function getRankings(): Collection
    {
        return $this->rankings;
    }

    public function addRanking(Ranking $ranking): static
    {
        if (!$this->rankings->contains($ranking)) {
            $this->rankings->add($ranking);
            $ranking->setCategoria($this);
        }

        return $this;
    }

    public function removeRanking(Ranking $ranking): static
    {
        if ($this->rankings->removeElement($ranking)) {
            // set the owning side to null (unless already changed)
            if ($ranking->getCategoria() === $this) {
                $ranking->setCategoria(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FotoAstral>
     */
    public function getFotoAstrals(): Collection
    {
        return $this->fotoAstrals;
    }

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
