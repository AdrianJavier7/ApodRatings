<?php

namespace App\Entity;

use App\Repository\RankingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingRepository::class)]
#[ORM\Table(name: 'ranking', schema: 'apodnasa')] // Especificamos la tabla y el esquema
class Ranking
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(targetEntity: Categoria::class)]
    #[ORM\JoinColumn(name: 'id_categoria', referencedColumnName: 'id', nullable: false)]
    private ?Categoria $categoria = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, RankingFoto>
     */
    #[ORM\OneToMany(targetEntity: RankingFoto::class, mappedBy: 'ranking')]
    private \Doctrine\Common\Collections\Collection $rankingFotos;

    public function __construct()
    {
        $this->rankingFotos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getRankingFotos(): \Doctrine\Common\Collections\Collection
    {
        return $this->rankingFotos;
    }
}
